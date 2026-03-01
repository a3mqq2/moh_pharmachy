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

    protected static $documentTypes = [
        'registration_forms' => 'نماذج التسجيل المعتمدة',
        'fda_certificate' => 'شهادة FDA',
        'ema_certificate' => 'شهادة EMA/EMEA',
        'cpp_fsc_certificate' => 'شهادة المنتج الصيدلاني (CPP/FSC)',
        'pricing_certificate' => 'شهادة الأسعار',
        'other_countries_registration' => 'شهادات تسجيل في دول أخرى',
        'drug_master_file' => 'الملف الرئيسي للدواء',
        'product_specifications' => 'مواصفات المنتج والتركيب',
        'active_ingredients_analysis' => 'شهادة تحليل المواد الفعالة',
        'packaging_specifications' => 'المواصفات الفنية للعبوات',
        'accelerated_stability_studies' => 'دراسات الثبوتية المُسرعة',
        'hot_climate_stability_studies' => 'دراسات الثبوتية للمناخ الحار',
        'pharmacology_toxicology_studies' => 'دراسات علم الأدوية والسموم',
        'bioequivalence_studies' => 'دراسات التكافؤ الحيوي',
        'product_labels' => 'ملصقات المنتج',
        'internal_leaflets' => 'النشرات الداخلية',
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
        return self::$documentTypes[$this->document_type] ?? $this->document_type;
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['بايت', 'كيلوبايت', 'ميجابايت', 'جيجابايت'];

        for ($i = 0; $bytes >= 1024 && $i < 3; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public static function getDocumentTypes(): array
    {
        return self::$documentTypes;
    }

    public static function getRequiredDocumentTypes(): array
    {
        return array_keys(self::$documentTypes);
    }

    public static function getMultipleUploadTypes(): array
    {
        return array_keys(self::$documentTypes);
    }
}
