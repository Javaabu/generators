<?php

namespace App\Http\Controllers\Customer\Auth;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Javaabu\Auth\User as UserContract;

class ResetPasswordController extends \Javaabu\Auth\Http\Controllers\Auth\ResetPasswordController
{
    public function getBroker(): PasswordBroker
    {
        return Password::broker('customers');
    }

    public function getResetFormViewName(): string
    {
        return 'customer.auth.passwords.reset';
    }

    public function determinePathForRedirectUsing(): UserContract
    {
        return new \App\Models\Customer();
    }
}
