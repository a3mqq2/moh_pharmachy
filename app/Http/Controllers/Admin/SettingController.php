<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'local_company_registration_fee' => Setting::get('local_company_registration_fee', 0),
            'local_company_renewal_fee' => Setting::get('local_company_renewal_fee', 0),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'local_company_registration_fee' => 'required|numeric|min:0',
            'local_company_renewal_fee' => 'required|numeric|min:0',
        ], [
            'local_company_registration_fee.required' => 'رسوم تسجيل الشركة المحلية مطلوبة',
            'local_company_registration_fee.numeric' => 'يجب أن تكون رسوم التسجيل رقماً',
            'local_company_registration_fee.min' => 'يجب أن تكون رسوم التسجيل قيمة موجبة',
            'local_company_renewal_fee.required' => 'رسوم تجديد الشركة المحلية مطلوبة',
            'local_company_renewal_fee.numeric' => 'يجب أن تكون رسوم التجديد رقماً',
            'local_company_renewal_fee.min' => 'يجب أن تكون رسوم التجديد قيمة موجبة',
        ]);

        Setting::set('local_company_registration_fee', $validated['local_company_registration_fee'], [
            'group' => 'local_companies',
            'type' => 'number',
            'label' => 'رسوم تسجيل شركة محلية',
        ]);

        Setting::set('local_company_renewal_fee', $validated['local_company_renewal_fee'], [
            'group' => 'local_companies',
            'type' => 'number',
            'label' => 'رسوم تجديد شركة محلية',
        ]);

        return redirect()->route('admin.settings.index')
            ->with('success', 'تم حفظ الإعدادات بنجاح');
    }
}
