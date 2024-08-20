<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Javaabu\Helpers\Traits\HasOrderbys;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductsRequest;
use App\Models\Product;
use App\Exports\ProductsExport;

class ProductsController extends Controller
{
    use HasOrderbys;

    /**
     * Create a new  controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Product::class);
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
            'name' => __('Name'),
            'address' => __('Address'),
            'slug' => __('Slug'),
            'price' => __('Price'),
            'stock' => __('Stock'),
            'published_at' => __('Published At'),
            'expire_at' => __('Expire At'),
            'released_on' => __('Released On'),
            'sale_time' => __('Sale Time'),
            'manufactured_year' => __('Manufactured Year'),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, bool $trashed = false)
    {
        $title = $trashed ? __('All Deleted Products') : __('All Products');
        $orderby = $this->getOrderBy($request, 'created_at');
        $order = $this->getOrder($request, 'created_at', $orderby);
        $per_page = $this->getPerPage($request);

        $products = Product::orderBy($orderby, $order);

        $search = null;
        if ($search = $request->input('search')) {
            $products->search($search);
            $title = __('Products matching \':search\'', ['search' => $search]);
        }

        if ($date_field = $request->input('date_field')) {
            $products->dateBetween($date_field, $request->input('date_from'), $request->input('date_to'));
        }

        if ($trashed) {
            $products->onlyTrashed();
        }

        $products->with('category');

        if ($request->download) {
            return (new ProductsExport($products))->download('products.xlsx');
        }

        $products = $products->paginate($per_page)
                             ->appends($request->except('page'));

        return view('admin.products.index', compact('products', 'title', 'per_page', 'search', 'trashed'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return view('admin.products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductsRequest $request)
    {
        $product = new Product($request->validated());

        if ($request->hasAny(['on_sale', 'sync_on_sale'])) {
            $product->on_sale = $request->input('on_sale', false);
        }

        if ($request->has('category')) {
            $product->category()->associate($request->input('category'));
        }

        $product->save();

        $this->flashSuccessMessage();

        return redirect()->action([ProductsController::class, 'edit'], $product);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductsRequest $request, Product $product)
    {
        $product->fill($request->validated());

        if ($request->hasAny(['on_sale', 'sync_on_sale'])) {
            $product->on_sale = $request->input('on_sale', false);
        }

        if ($request->has('category')) {
            $product->category()->associate($request->input('category'));
        }

        $product->save();

        $this->flashSuccessMessage();

        return redirect()->action([ProductsController::class, 'edit'], $product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, Request $request)
    {
        $product->delete();

        if ($request->expectsJson()) {
            return response()->json(true);
        }

        return redirect()->action([ProductsController::class, 'index']);
    }

    /**
     * Display a listing of the deleted resources.
     */
    public function trash(Request $request)
    {
        $this->authorize('viewTrash', Product::class);

        return $this->index($request, true);
    }

    /**
     * Force delete the resource
     */
    public function forceDelete(Product $product, Request $request)
    {
        // make sure it is trashed
        if (! $product->trashed()) {
            abort(404);
        }

        $this->authorize('forceDelete', $product);

        $product->forceDelete();

        if ($request->expectsJson()) {
            return response()->json(true);
        }

        return redirect()->action([ProductsController::class, 'trash']);
    }

    /**
     * Restore deleted resource
     */
    public function restore(Product $product, Request $request)
    {
        // make sure it is trashed
        if (! $product->trashed()) {
            abort(404);
        }

        $this->authorize('restore', $product);

        $product->restore();

        if ($request->expectsJson()) {
            return response()->json(true);
        }

        return redirect()->action([ProductsController::class, 'trash']);
    }

    /**
     * Perform bulk action on the resource
     */
    public function bulk(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $this->validate($request, [
            'action' => 'required|in:delete',
            'products' => 'required|array',
            'products.*' => 'exists:products,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('products', []);

        switch ($action) {
            case 'delete':
                // make sure allowed to delete
                $this->authorize('delete_products');

                Product::whereIn('id', $ids)
                    ->get()
                    ->each(function (Product $product) {
                        $product->delete();
                    });
                break;
        }

        $this->flashSuccessMessage();

        return $this->redirect($request, action([ProductsController::class, 'index']));
    }
}
