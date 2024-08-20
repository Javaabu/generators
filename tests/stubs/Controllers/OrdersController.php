<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Javaabu\Helpers\Traits\HasOrderbys;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrdersRequest;
use App\Models\Order;
use App\Exports\OrdersExport;

class OrdersController extends Controller
{
    use HasOrderbys;

    /**
     * Create a new  controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Order::class);
    }

    /**
     * Initialize orderbys
     */
    protected static function initOrderbys()
    {
        static::$orderbys = [
            'id' => __('Id'),
            'created_at' => __('Created At'),
            'updated_at' => __('Updated At'),
            'order_no' => __('Order No'),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = __('All Orders');
        $orderby = $this->getOrderBy($request, 'created_at');
        $order = $this->getOrder($request, 'created_at', $orderby);
        $per_page = $this->getPerPage($request);

        $orders = Order::orderBy($orderby, $order);

        $search = null;
        if ($search = $request->input('search')) {
            $orders->search($search);
            $title = __('Orders matching \':search\'', ['search' => $search]);
        }

        if ($date_field = $request->input('date_field')) {
            $orders->dateBetween($date_field, $request->input('date_from'), $request->input('date_to'));
        }

        $orders->with('category', 'productSlug');

        if ($request->download) {
            return (new OrdersExport($orders))->download('orders.xlsx');
        }

        $orders = $orders->paginate($per_page)
                         ->appends($request->except('page'));

        return view('admin.orders.index', compact('orders', 'title', 'per_page', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return view('admin.orders.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrdersRequest $request)
    {
        $order = new Order($request->validated());

        if ($request->has('category')) {
            $order->category()->associate($request->input('category'));
        }

        if ($request->has('product_slug')) {
            $order->productSlug()->associate($request->input('product_slug'));
        }

        $order->save();

        $this->flashSuccessMessage();

        return redirect()->action([OrdersController::class, 'edit'], $order);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrdersRequest $request, Order $order)
    {
        $order->fill($request->validated());

        if ($request->has('category')) {
            $order->category()->associate($request->input('category'));
        }

        if ($request->has('product_slug')) {
            $order->productSlug()->associate($request->input('product_slug'));
        }

        $order->save();

        $this->flashSuccessMessage();

        return redirect()->action([OrdersController::class, 'edit'], $order);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order, Request $request)
    {
        $order->delete();

        if ($request->expectsJson()) {
            return response()->json(true);
        }

        return redirect()->action([OrdersController::class, 'index']);
    }

    /**
     * Perform bulk action on the resource
     */
    public function bulk(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        $this->validate($request, [
            'action' => 'required|in:delete',
            'orders' => 'required|array',
            'orders.*' => 'exists:orders,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('orders', []);

        switch ($action) {
            case 'delete':
                // make sure allowed to delete
                $this->authorize('delete_orders');

                Order::whereIn('id', $ids)
                    ->get()
                    ->each(function (Order $order) {
                        $order->delete();
                    });
                break;
        }

        $this->flashSuccessMessage();

        return $this->redirect($request, action([OrdersController::class, 'index']));
    }
}
