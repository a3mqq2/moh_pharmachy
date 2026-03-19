<?php

namespace App\Http\Controllers;

use App\Models\LocalCompany;
use App\Models\ForeignCompany;
use App\Models\PharmaceuticalProduct;

class VerificationController extends Controller
{
    public function localCompany(LocalCompany $localCompany)
    {
        return view('verification.show', [
            'type' => 'local-company',
            'title' => 'التحقق من شهادة تسجيل شركة محلية',
            'entity' => $localCompany,
            'fields' => [
                'اسم الشركة' => $localCompany->company_name,
                'نوع الشركة' => $localCompany->company_type_name,
                'المجال' => $localCompany->license_specialty_name,
                'رقم التسجيل' => $localCompany->registration_number,
                'تاريخ التسجيل' => $localCompany->registration_date?->format('Y-m-d'),
                'تاريخ الانتهاء' => $localCompany->expires_at?->format('Y-m-d'),
            ],
            'status' => $localCompany->status_name,
            'statusColor' => $localCompany->status_color,
        ]);
    }

    public function foreignCompany(ForeignCompany $foreignCompany)
    {
        return view('verification.show', [
            'type' => 'foreign-company',
            'title' => 'التحقق من شهادة تسجيل شركة أجنبية',
            'entity' => $foreignCompany,
            'fields' => [
                'Company Name' => $foreignCompany->company_name,
                'Country' => $foreignCompany->country,
                'Activity Type' => $foreignCompany->activity_type,
                'Registration No.' => $foreignCompany->registration_number,
                'Local Agent' => $foreignCompany->localCompany?->company_name ?? '-',
                'Date of Issue' => $foreignCompany->approved_at?->format('Y-m-d'),
                'Expiry Date' => $foreignCompany->expires_at?->format('Y-m-d'),
            ],
            'status' => $foreignCompany->status_name,
            'statusColor' => match($foreignCompany->status) {
                'active', 'approved' => 'success',
                'rejected' => 'danger',
                'suspended' => 'secondary',
                'expired' => 'danger',
                default => 'warning',
            },
        ]);
    }

    public function pharmaceuticalProduct(PharmaceuticalProduct $product)
    {
        return view('verification.show', [
            'type' => 'pharmaceutical-product',
            'title' => 'التحقق من شهادة تسجيل مستحضر دوائي',
            'entity' => $product,
            'fields' => [
                'Trade Name' => $product->trade_name ?? $product->product_name,
                'Scientific Name' => $product->scientific_name,
                'Manufacturer' => $product->foreignCompany?->company_name,
                'Country of Origin' => $product->origin,
                'Dosage Form' => $product->pharmaceutical_form,
                'Concentration' => $product->concentration,
                'Registration No.' => $product->registration_number,
                'Date of Registration' => $product->final_approved_at?->format('Y-m-d'),
            ],
            'status' => $product->status_name,
            'statusColor' => match($product->status) {
                'active' => 'success',
                'rejected' => 'danger',
                default => 'warning',
            },
        ]);
    }
}
