<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ForeignCompanyDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'foreign_company_id',
        'document_type',
        'document_name',
        'file_path',
        'file_size',
        'mime_type',
        'notes',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    // Relations
    public function foreignCompany(): BelongsTo
    {
        return $this->belongsTo(ForeignCompany::class);
    }

    // Accessors
    public function getDocumentTypeNameAttribute(): string
    {
        return match($this->document_type) {
            'official_registration_request' => 'طلب تسجيل رسمي من الشركة المصنعة',
            'agency_agreement' => 'رسالة وكالة أو اتفاقية توزيع',
            'registration_forms' => 'نماذج التسجيل المعتمدة',
            'gmp_certificate' => 'شهادة GMP',
            'fda_certificate' => 'شهادة FDA',
            'emea_certificate' => 'شهادة EMEA',
            'cpp_certificate' => 'شهادة المنتج الصيدلاني (CPP)',
            'fsc_certificate' => 'شهادة البيع الحر (FSC)',
            'manufacturing_license' => 'ترخيص تصنيع ساري',
            'financial_report' => 'تقرير مالي لآخر سنتين',
            'products_list' => 'قائمة منتجات الشركة',
            'site_master_file' => 'الملف الرئيسي للمصنع',
            'registration_certificates' => 'شهادات تسجيل في دول أخرى',
            'exclusive_agency_contract' => 'عقد الوكالة الحصري',
            'other' => 'مستندات أخرى',
            default => $this->document_type,
        };
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) {
            return 'غير محدد';
        }

        $bytes = floatval($this->file_size);

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    // Methods
    public function exists(): bool
    {
        return !empty($this->file_path) && Storage::disk('public')->exists($this->file_path);
    }

    public function getDownloadUrl(): string
    {
        return route('representative.foreign-companies.documents.download', [
            'company' => $this->foreign_company_id,
            'document' => $this->id
        ]);
    }

    public function delete(): ?bool
    {
        // Delete the file from storage
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }

        return parent::delete();
    }

    // Static methods
    public function updateRequests()
    {
        return $this->morphMany(DocumentUpdateRequest::class, 'documentable');
    }

    public function pendingUpdateRequest()
    {
        return $this->morphOne(DocumentUpdateRequest::class, 'documentable')->where('status', 'pending');
    }

    public static function getDocumentTypes(): array
    {
        return [
            'official_registration_request' => 'طلب تسجيل رسمي من الشركة المصنعة',
            'agency_agreement' => 'رسالة وكالة أو اتفاقية توزيع',
            'registration_forms' => 'نماذج التسجيل المعتمدة',
            'gmp_certificate' => 'شهادة GMP',
            'fda_certificate' => 'شهادة FDA',
            'emea_certificate' => 'شهادة EMEA',
            'cpp_certificate' => 'شهادة المنتج الصيدلاني (CPP)',
            'fsc_certificate' => 'شهادة البيع الحر (FSC)',
            'manufacturing_license' => 'ترخيص تصنيع ساري',
            'financial_report' => 'تقرير مالي لآخر سنتين',
            'products_list' => 'قائمة منتجات الشركة',
            'site_master_file' => 'الملف الرئيسي للمصنع',
            'registration_certificates' => 'شهادات تسجيل في دول أخرى',
            'exclusive_agency_contract' => 'عقد الوكالة الحصري',
            'other' => 'مستندات أخرى',
        ];
    }

    public static function getRequiredDocumentTypes(): array
    {
        return [
            // Core 9 required documents
            'official_registration_request',
            'agency_agreement',
            'registration_forms',
            'gmp_certificate',
            'manufacturing_license',
            'financial_report',
            'products_list',
            'site_master_file',
            'exclusive_agency_contract',
            // FDA or EMEA (at least one required)
            'fda_certificate', // OR
            'emea_certificate', // OR
        ];
    }

    public static function getOptionalDocumentTypes(): array
    {
        return [
            'cpp_certificate',
            'fsc_certificate',
            'registration_certificates',
            'other',
        ];
    }
}
