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


    public function foreignCompany(): BelongsTo
    {
        return $this->belongsTo(ForeignCompany::class);
    }


    public function getDocumentTypeNameAttribute(): string
    {
        $types = self::getDocumentTypes();
        return $types[$this->document_type] ?? $this->document_type;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) {
            return __('general.not_specified');
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

        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }

        return parent::delete();
    }


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
            'official_registration_request' => __('documents.foreign_type_official_registration_request'),
            'agency_agreement' => __('documents.foreign_type_agency_agreement'),
            'registration_forms' => __('documents.foreign_type_registration_forms'),
            'gmp_certificate' => __('documents.foreign_type_gmp_certificate'),
            'fda_certificate' => __('documents.foreign_type_fda_certificate'),
            'emea_certificate' => __('documents.foreign_type_emea_certificate'),
            'cpp_certificate' => __('documents.foreign_type_cpp_certificate'),
            'fsc_certificate' => __('documents.foreign_type_fsc_certificate'),
            'manufacturing_license' => __('documents.foreign_type_manufacturing_license'),
            'financial_report' => __('documents.foreign_type_financial_report'),
            'products_list' => __('documents.foreign_type_products_list'),
            'site_master_file' => __('documents.foreign_type_site_master_file'),
            'registration_certificates' => __('documents.foreign_type_registration_certificates'),
            'exclusive_agency_contract' => __('documents.foreign_type_exclusive_agency_contract'),
            'products_artwork_list' => __('documents.foreign_type_products_artwork_list'),
            'pv_master_file' => __('documents.foreign_type_pv_master_file'),
            'other' => __('documents.type_other_docs'),
        ];
    }

    public static function getRequiredDocumentTypes(): array
    {
        return [
            'official_registration_request',
            'agency_agreement',
            'registration_forms',
            'gmp_certificate',
            'manufacturing_license',
            'financial_report',
            'products_list',
            'site_master_file',
            'exclusive_agency_contract',
            'products_artwork_list',
            'pv_master_file',
        ];
    }

    public static function getOptionalDocumentTypes(): array
    {
        return [
            'fda_certificate',
            'emea_certificate',
            'cpp_certificate',
            'fsc_certificate',
            'registration_certificates',
            'other',
        ];
    }
}
