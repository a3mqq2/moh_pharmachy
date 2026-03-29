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
            'local_company_registration_fee.required' => __('settings.validation_registration_fee_required'),
            'local_company_registration_fee.numeric' => __('settings.validation_registration_fee_numeric'),
            'local_company_registration_fee.min' => __('settings.validation_registration_fee_min'),
            'local_company_renewal_fee.required' => __('settings.validation_renewal_fee_required'),
            'local_company_renewal_fee.numeric' => __('settings.validation_renewal_fee_numeric'),
            'local_company_renewal_fee.min' => __('settings.validation_renewal_fee_min'),
        ]);

        Setting::set('local_company_registration_fee', $validated['local_company_registration_fee'], [
            'group' => 'local_companies',
            'type' => 'number',
            'label' => __('settings.local_company_registration_fee_label'),
        ]);

        Setting::set('local_company_renewal_fee', $validated['local_company_renewal_fee'], [
            'group' => 'local_companies',
            'type' => 'number',
            'label' => __('settings.local_company_renewal_fee_label'),
        ]);

        return redirect()->route('admin.settings.index')
            ->with('success', __('settings.settings_updated'));
    }
}
