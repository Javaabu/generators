<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Javaabu\Auth\Enums\UserStatuses;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class RegisterController extends \Javaabu\Auth\Http\Controllers\Auth\RegisterController
{
    public function determinePathForRedirectUsing(): \Javaabu\Auth\User
    {
        return new Customer();
    }

    public function showRegistrationForm()
    {
        return view('customer.auth.register');
    }

    protected function validator(array $data)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('customers', 'email')],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
            'designation' => ['required', Rule::unique('customers', 'designation'), 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'expire_at' => ['required', 'date'],
            'agreement' => ['accepted'],
        ];

        if (! app()->runningUnitTests()) {
            $rules[recaptchaFieldName()] = recaptchaRuleName();
        }

        return Validator::make($data, $rules);
    }

    /**
     * Create a new user instance after a valid registration.
     */
    public function create(array $data)
    {
        $class = $this->userClass();

        $customer = new $class();

        $customer->name = $data['name'];
        $customer->email = $data['email'];
        $customer->password = $data['password'];
        $customer->status = UserStatuses::APPROVED;
        $customer->designation = $data['designation'];
        $customer->address = $data['address'];
        $customer->expire_at = $data['expire_at'];

        $customer->save();

        return $customer;
    }
}
