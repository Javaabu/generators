<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Javaabu\Auth\Notifications\NewPasswordNotification;
use Javaabu\Helpers\Traits\HasOrderbys;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomersRequest;
use App\Models\Customer;
use App\Exports\CustomersExport;

class CustomersController extends Controller
{
    use HasOrderbys;

    /**
     * Create a new  controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Customer::class);
    }

    /**
     * Initialize orderbys
     */
    protected static function initOrderbys()
    {
        static::$orderbys = [
            'id' => __('Id'),
            'name' => __('Name'),
            'email' => __('Email'),
            'created_at' => __('Created At'),
            'updated_at' => __('Updated At'),
            'designation' => __('Designation'),
            'address' => __('Address'),
            'expire_at' => __('Expire At'),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, bool $trashed = false)
    {
        $title = $trashed ? __('All Deleted Customers') : __('All Customers');
        $orderby = $this->getOrderBy($request, 'created_at');
        $order = $this->getOrder($request, 'created_at', $orderby);
        $per_page = $this->getPerPage($request);

        $customers = Customer::orderBy($orderby, $order);

        $search = null;
        if ($search = $request->input('search')) {
            $customers->search($search);
            $title = __('Customers matching \':search\'', ['search' => $search]);
        }

        if ($status = $request->input('status')) {
            $customers->whereStatus($status);
        }

        if ($request->filled('locked_out')) {
            if ($request->input('locked_out')) {
                $customers->lockedOut();
            } else {
                $customers->notLockedOut();
            }
        }

        if ($date_field = $request->input('date_field')) {
            $customers->dateBetween($date_field, $request->input('date_from'), $request->input('date_to'));
        }

        if ($trashed) {
            $customers->onlyTrashed();
        }

        $customers->with('media', 'category');

        if ($request->download) {
            return (new CustomersExport($customers))->download('customers.xlsx');
        }

        $customers = $customers->paginate($per_page)
                               ->appends($request->except('page'));

        return view('admin.customers.index', compact('customers', 'title', 'per_page', 'search', 'trashed'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomersRequest $request)
    {
        $customer = new Customer($request->validated());
        $customer->email = $request->input('email');
        $customer->setEmailVerificationStatus($request->input('email_verified', false));

        if ($request->hasAny(['on_sale', 'sync_on_sale'])) {
            $customer->on_sale = $request->input('on_sale', false);
        }

        if ($request->has('category')) {
            $customer->category()->associate($request->input('category'));
        }

        $password = $request->input('password');

        if ($password) {
            $customer->password = $password;
            $customer->require_password_update = $request->input('require_password_update', false);
        }

        if ($request->user()->can('approve', Customer::class)) {
            $customer->approve();
        } else {
            $customer->markAsPending();
        }

        $customer->save();

        $customer->updateSingleMedia('avatar', $request);

        if ($password && $request->send_password) {
            $customer->notify(new NewPasswordNotification($password));
        }

        $this->flashSuccessMessage();

        return redirect()->action([CustomersController::class, 'edit'], $customer);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomersRequest $request, Customer $customer)
    {
        if ($action = $request->input('action')) {
            switch ($action) {
                case 'resend_verification':
                    $this->authorize('resendVerification', $customer);
                    $customer->resendEmailVerification();

                    flash_push('alerts', [
                        'text' => __('Email verification resent successfully.'),
                        'type' => 'success',
                        'title' => __('Success!'),
                    ]);
                    break;

                case 'approve':
                    $this->authorize('approve', Customer::class);
                    $customer->approve();
                    break;

                case 'ban':
                    $this->authorize('approve', Customer::class);
                    $customer->ban();
                    break;

                case 'mark_pending':
                    $this->authorize('approve', Customer::class);
                    $customer->markAsPending();
                    break;

                case 'update_password':
                    // update password
                    if ($password = $request->input('password')) {
                        $customer->password = $password;

                        if ($request->input('require_password_update')) {
                            $customer->require_password_update = true;
                        } else {
                            $customer->require_password_update = false;
                        }

                        $customer->resetLoginAttempts();

                        if ($password && $request->send_password) {
                            $customer->notify(new NewPasswordNotification($password));
                        }

                        flash_push('alerts', [
                            'text' => __('Password changed successfully'),
                            'type' => 'success',
                            'title' => __('Success!'),
                        ]);
                    }
                    break;
            }
        } else {
            $customer->fill($request->validated());

            if ($request->hasAny(['on_sale', 'sync_on_sale'])) {
                $customer->on_sale = $request->input('on_sale', false);
            }

            if ($request->has('category')) {
                $customer->category()->associate($request->input('category'));
            }

            if ($email = $request->input('email')) {
                $customer->email = $email;
            }

            if ($request->filled('email_verified')) {
                $customer->setEmailVerificationStatus($request->email_verified);
            }

            $customer->updateSingleMedia('avatar', $request);
        }

        $customer->save();

        $this->flashSuccessMessage();

        return redirect()->action([CustomersController::class, 'edit'], $customer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer, Request $request)
    {
        $customer->delete();

        if ($request->expectsJson()) {
            return response()->json(true);
        }

        return redirect()->action([CustomersController::class, 'index']);
    }

    /**
     * Display a listing of the deleted resources.
     */
    public function trash(Request $request)
    {
        $this->authorize('viewTrash', Customer::class);

        return $this->index($request, true);
    }

    /**
     * Force delete the resource
     */
    public function forceDelete(Customer $customer, Request $request)
    {
        // make sure it is trashed
        if (! $customer->trashed()) {
            abort(404);
        }

        $this->authorize('forceDelete', $customer);

        $customer->forceDelete();

        if ($request->expectsJson()) {
            return response()->json(true);
        }

        return redirect()->action([CustomersController::class, 'trash']);
    }

    /**
     * Restore deleted resource
     */
    public function restore(Customer $customer, Request $request)
    {
        // make sure it is trashed
        if (! $customer->trashed()) {
            abort(404);
        }

        $this->authorize('restore', $customer);

        $customer->restore();

        if ($request->expectsJson()) {
            return response()->json(true);
        }

        return redirect()->action([CustomersController::class, 'trash']);
    }

    /**
     * Perform bulk action on the resource
     */
    public function bulk(Request $request)
    {
        $this->authorize('viewAny', Customer::class);

        $this->validate($request, [
            'action' => 'required|in:delete,approve,ban',
            'customers' => 'required|array',
            'customers.*' => 'exists:customers,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('customers', []);

        switch ($action) {
            case 'delete':
                // make sure allowed to delete
                $this->authorize('delete_customers');

                Customer::whereIn('id', $ids)
                    ->get()
                    ->each(function (Customer $customer) {
                        $customer->delete();
                    });
                break;

            case 'ban':
            case 'approve':
                // make sure allowed to approve
                $this->authorize('approve', Customer::class);

                Customer::whereIn('id', $ids)
                    ->get()
                    ->each(function (Customer $customer) use ($action) {
                        $customer->{$action}();
                        $customer->save();
                    });
                break;
        }

        $this->flashSuccessMessage();

        return $this->redirect($request, action([CustomersController::class, 'index']));
    }
}
