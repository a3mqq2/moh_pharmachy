<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function settings()
    {
        $representative = Auth::guard('representative')->user();
        return view('representative.settings', compact('representative'));
    }

    public function updatePassword(Request $request)
    {
        $representative = Auth::guard('representative')->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => __('auth.validation_current_password_required'),
            'new_password.required' => __('auth.validation_new_password_required'),
            'new_password.confirmed' => __('auth.validation_password_confirmed'),
            'new_password.min' => __('auth.validation_password_min'),
        ]);

        if (!Hash::check($request->current_password, $representative->password)) {
            return back()->withErrors(['current_password' => __('auth.password_incorrect')]);
        }

        if (Hash::check($request->new_password, $representative->password)) {
            return back()->withErrors(['new_password' => __('auth.validation_password_same')]);
        }

        $representative->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', __('auth.password_changed'));
    }

    public function updateProfile(Request $request)
    {
        $representative = Auth::guard('representative')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ], [
            'name.required' => __('auth.validation_name_required'),
            'job_title.required' => __('auth.validation_job_title_required'),
            'phone.required' => __('auth.validation_phone_required'),
        ]);

        $representative->update([
            'name' => $request->name,
            'job_title' => $request->job_title,
            'phone' => $request->phone,
        ]);

        return back()->with('success', __('auth.profile_updated'));
    }
}
