<?php

namespace App\Http\Controllers\Customer\Auth;

use Illuminate\View\View;
use Javaabu\Auth\User as UserContract;

class LoginController extends \Javaabu\Auth\Http\Controllers\Auth\LoginController
{
    public function getLoginForm(): View
    {
        return view('customer.auth.login');
    }

    public function determinePathForRedirectUsing(): UserContract
    {
        return new \App\Models\Customer();
    }
}
