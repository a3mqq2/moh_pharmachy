<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    
    public function login()
    {
        return view('auth.login');
    }

   
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => __('auth.validation_email_required_admin'),
            'email.email' => __('auth.validation_email_invalid_admin'),
            'password.required' => __('auth.validation_password_required_admin'),
            'password.min' => __('auth.validation_password_min_admin'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {

            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', __('auth.welcome_back'));
        }

        return redirect()->back()
            ->withInput($request->except('password'))
            ->with('error', __('auth.invalid_credentials'));
    }
}