<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PharmaceuticalProductDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pharmaceutical_product_id',
        'document_type',
        'file_path',
        'original_name',
        'file_size',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    protected static $documentTypeKeys = [
        'registration_forms',
        'fda_certificate',
        'ema_certificate',
        'cpp_fsc_certificate',
        'pricing_certificate',
        'other_countries_registration',
        'drug_master_file',
        'product_specifications',
        'active_ingredients_analysis',
        'packaging_specifications',
        'accelerated_stability_studies',
        'hot_climate_stability_studies',
        'pharmacology_toxicology_studies',
        'bioequivalence_studies',
        'product_labels',
        'internal_leaflets',
    ];

    public function pharmaceuticalProduct(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalProduct::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getDocumentTypeNameAttribute(): string
    {
        $types = self::getDocumentTypes();
        return $types[$this->document_type] ?? $this->document_type;
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        $units = [__('documents.size_bytes'), __('documents.size_kb'), __('documents.size_mb'), __('documents.size_gb')];

        for ($i = 0; $bytes >= 1024 && $i < 3; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function updateRequests()
    {
        return $this->morphMany(\App\Models\DocumentUpdateRequest::class, 'documentable');
    }

    public function pendingUpdateRequest()
    {
        return $this->morphOne(\App\Models\DocumentUpdateRequest::class, 'documentable')->where('status', 'pending');
    }

    public static function getDocumentTypes(): array
    {
        return [
            'registration_forms' => __('documents.pharma_type_registration_forms'),
            'fda_certificate' => __('documents.pharma_type_fda_certificate'),
            'ema_certificate' => __('documents.pharma_type_ema_certificate'),
            'cpp_fsc_certificate' => __('documents.pharma_type_cpp_fsc_certificate'),
            'pricing_certificate' => __('documents.pharma_type_pricing_certificate'),
            'other_countries_registration' => __('documents.pharma_type_other_countries_registration'),
            'drug_master_file' => __('documents.pharma_type_drug_master_file'),
            'product_specifications' => __('documents.pharma_type_product_specifications'),
            'active_ingredients_analysis' => __('documents.pharma_type_active_ingredients_analysis'),
            'packaging_specifications' => __('documents.pharma_type_packaging_specifications'),
            'accelerated_stability_studies' => __('documents.pharma_type_accelerated_stability_studies'),
            'hot_climate_stability_studies' => __('documents.pharma_type_hot_climate_stability_studies'),
            'pharmacology_toxicology_studies' => __('documents.pharma_type_pharmacology_toxicology_studies'),
            'bioequivalence_studies' => __('documents.pharma_type_bioequivalence_studies'),
            'product_labels' => __('documents.pharma_type_product_labels'),
            'internal_leaflets' => __('documents.pharma_type_internal_leaflets'),
        ];
    }

    public static function getRequiredDocumentTypes(): array
    {
        return [
            'registration_forms',
            'drug_master_file',
            'product_specifications',
            'active_ingredients_analysis',
            'packaging_specifications',
            'accelerated_stability_studies',
            'hot_climate_stability_studies',
            'pharmacology_toxicology_studies',
            'bioequivalence_studies',
            'product_labels',
            'internal_leaflets',
        ];
    }

    public static function getOptionalDocumentTypes(): array
    {
        return [
            'fda_certificate',
            'ema_certificate',
            'cpp_fsc_certificate',
            'pricing_certificate',
            'other_countries_registration',
        ];
    }

    public static function getMultipleUploadTypes(): array
    {
        return self::$documentTypeKeys;
    }
}
