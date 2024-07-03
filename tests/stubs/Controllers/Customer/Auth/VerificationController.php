<?php

namespace App\Http\Controllers\Customer\Auth;

use Javaabu\Auth\User as UserContract;

class VerificationController extends \Javaabu\Auth\Http\Controllers\Auth\VerificationController
{
    #[\Override]
    public function getEmailVerificationView()
    {
        return view('customer.auth.verification.verify');
    }

    #[\Override]
    public function getVerificationResultView()
    {
        return view('customer.auth.verification.result');
    }

    public function determinePathForRedirectUsing(): UserContract
    {
        return new \App\Models\Customer();
    }
}
