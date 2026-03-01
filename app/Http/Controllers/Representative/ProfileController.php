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
            'current_password.required' => 'يرجى إدخال كلمة المرور الحالية',
            'new_password.required' => 'يرجى إدخال كلمة المرور الجديدة',
            'new_password.confirmed' => 'كلمة المرور الجديدة غير متطابقة',
            'new_password.min' => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل',
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $representative->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة']);
        }

        // Check if new password is same as current
        if (Hash::check($request->new_password, $representative->password)) {
            return back()->withErrors(['new_password' => 'كلمة المرور الجديدة يجب أن تكون مختلفة عن الحالية']);
        }

        // Update password
        $representative->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح');
    }

    public function updateProfile(Request $request)
    {
        $representative = Auth::guard('representative')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ], [
            'name.required' => 'يرجى إدخال الاسم',
            'job_title.required' => 'يرجى إدخال المسمى الوظيفي',
            'phone.required' => 'يرجى إدخال رقم الهاتف',
        ]);

        $representative->update([
            'name' => $request->name,
            'job_title' => $request->job_title,
            'phone' => $request->phone,
        ]);

        return back()->with('success', 'تم تحديث المعلومات الشخصية بنجاح');
    }
}
