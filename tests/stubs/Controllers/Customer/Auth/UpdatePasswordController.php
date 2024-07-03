<?php

namespace App\Http\Controllers\Customer\Auth;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Javaabu\Auth\User as UserContract;

class UpdatePasswordController extends \Javaabu\Auth\Http\Controllers\Auth\UpdatePasswordController
{
    #[\Override]
    public function getBroker(): PasswordBroker
    {
        return Password::broker('customers');
    }

    #[\Override]
    public function getPasswordUpdateForm(): View
    {
        return view('customer.auth.passwords.update');
    }

    public function determinePathForRedirectUsing(): UserContract
    {
        return new \App\Models\Customer();
    }
}
