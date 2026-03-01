<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForeignCompany extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'representative_id',
        'local_company_id',
        'company_name',
        'country',
        'entity_type',
        'address',
        'email',
        'activity_type',
        'products_count',
        'registered_countries',
        'status',
        'rejection_reason',
        'approved_at',
        'activated_at',
        'approved_by',
        'expires_at',
        'last_renewed_at',
    ];

    protected $casts = [
        'registered_countries' => 'array',
        'products_count' => 'integer',
        'approved_at' => 'datetime',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_renewed_at' => 'datetime',
    ];

    // Relations
    public function representative(): BelongsTo
    {
        return $this->belongsTo(CompanyRepresentative::class, 'representative_id');
    }

    public function localCompany(): BelongsTo
    {
        return $this->belongsTo(LocalCompany::class, 'local_company_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ForeignCompanyDocument::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(ForeignCompanyInvoice::class);
    }

    public function pharmaceuticalProducts(): HasMany
    {
        return $this->hasMany(PharmaceuticalProduct::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeUploadingDocuments($query)
    {
        return $query->where('status', 'uploading_documents');
    }

    public function scopePendingPayment($query)
    {
        return $query->where('status', 'pending_payment');
    }

    // Accessors
    public function getEntityTypeNameAttribute(): string
    {
        return match($this->entity_type) {
            'company' => 'شركة',
            'factory' => 'مصنع',
            default => $this->entity_type,
        };
    }

    public function getActivityTypeNameAttribute(): string
    {
        return match($this->activity_type) {
            'medicines' => 'أدوية',
            'medical_supplies' => 'مستلزمات طبية',
            'both' => 'أدوية ومستلزمات طبية',
            default => $this->activity_type,
        };
    }

    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'uploading_documents' => 'قيد رفع المستندات',
            'pending' => 'قيد المراجعة',
            'pending_payment' => 'قيد السداد',
            'approved' => 'مقبولة',
            'active' => 'مفعلة',
            'rejected' => 'مرفوضة',
            'suspended' => 'معلقة',
            'expired' => 'منتهية الصلاحية',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'uploading_documents' => 'badge-info',
            'pending' => 'badge-warning',
            'pending_payment' => 'badge-warning',
            'approved' => 'badge-success',
            'active' => 'badge-success',
            'rejected' => 'badge-danger',
            'suspended' => 'badge-secondary',
            'expired' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function getCountryEnAttribute(): string
    {
        $countries = [
            'الإمارات العربية المتحدة' => 'United Arab Emirates',
            'المملكة العربية السعودية' => 'Saudi Arabia',
            'مصر' => 'Egypt',
            'الأردن' => 'Jordan',
            'لبنان' => 'Lebanon',
            'سوريا' => 'Syria',
            'العراق' => 'Iraq',
            'الكويت' => 'Kuwait',
            'قطر' => 'Qatar',
            'البحرين' => 'Bahrain',
            'عمان' => 'Oman',
            'اليمن' => 'Yemen',
            'المغرب' => 'Morocco',
            'الجزائر' => 'Algeria',
            'تونس' => 'Tunisia',
            'فلسطين' => 'Palestine',
            'السودان' => 'Sudan',
            'الصومال' => 'Somalia',
            'جيبوتي' => 'Djibouti',
            'موريتانيا' => 'Mauritania',
            'تركيا' => 'Turkey',
            'إيران' => 'Iran',
            'باكستان' => 'Pakistan',
            'الهند' => 'India',
            'الصين' => 'China',
            'اليابان' => 'Japan',
            'كوريا الجنوبية' => 'South Korea',
            'ماليزيا' => 'Malaysia',
            'إندونيسيا' => 'Indonesia',
            'تايلاند' => 'Thailand',
            'فيتنام' => 'Vietnam',
            'سنغافورة' => 'Singapore',
            'الفلبين' => 'Philippines',
            'بنغلاديش' => 'Bangladesh',
            'أفغانستان' => 'Afghanistan',
            'المملكة المتحدة' => 'United Kingdom',
            'ألمانيا' => 'Germany',
            'فرنسا' => 'France',
            'إيطاليا' => 'Italy',
            'إسبانيا' => 'Spain',
            'هولندا' => 'Netherlands',
            'بلجيكا' => 'Belgium',
            'سويسرا' => 'Switzerland',
            'النمسا' => 'Austria',
            'السويد' => 'Sweden',
            'النرويج' => 'Norway',
            'الدنمارك' => 'Denmark',
            'فنلندا' => 'Finland',
            'اليونان' => 'Greece',
            'البرتغال' => 'Portugal',
            'بولندا' => 'Poland',
            'رومانيا' => 'Romania',
            'أوكرانيا' => 'Ukraine',
            'روسيا' => 'Russia',
            'الولايات المتحدة' => 'United States',
            'كندا' => 'Canada',
            'المكسيك' => 'Mexico',
            'البرازيل' => 'Brazil',
            'الأرجنتين' => 'Argentina',
            'تشيلي' => 'Chile',
            'كولومبيا' => 'Colombia',
            'بيرو' => 'Peru',
            'فنزويلا' => 'Venezuela',
            'أستراليا' => 'Australia',
            'نيوزيلندا' => 'New Zealand',
            'جنوب أفريقيا' => 'South Africa',
            'نيجيريا' => 'Nigeria',
            'كينيا' => 'Kenya',
            'إثيوبيا' => 'Ethiopia',
            'غانا' => 'Ghana',
            'تنزانيا' => 'Tanzania',
            'أوغندا' => 'Uganda',
            'الكاميرون' => 'Cameroon',
            'ساحل العاج' => 'Ivory Coast',
        ];

        return $countries[$this->country] ?? $this->country;
    }

    // Methods
    public function markAsApproved(?int $approvedBy = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy ?? (auth()->check() ? auth()->id() : null),
            'rejection_reason' => null,
        ]);
    }

    public function markAsActive(): bool
    {
        $validityYears = (int) (Setting::where('key', 'foreign_company_validity_years')->first()?->value ?? 5);

        return $this->update([
            'status' => 'active',
            'activated_at' => now(),
            'expires_at' => now()->addYears($validityYears),
        ]);
    }

    public function markAsRejected(string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    public function markAsPendingPayment(): bool
    {
        return $this->update([
            'status' => 'pending_payment',
        ]);
    }

    public function markAsPending(): bool
    {
        return $this->update([
            'status' => 'pending',
        ]);
    }

    public function canUploadDocuments(): bool
    {
        return in_array($this->status, ['uploading_documents', 'rejected']);
    }

    public function canEdit(): bool
    {
        return in_array($this->status, ['rejected', 'uploading_documents']);
    }

    public function hasAllRequiredDocuments(): bool
    {
        // Required document types (9 core documents)
        $requiredDocumentTypes = [
            'official_registration_request',
            'agency_agreement',
            'registration_forms',
            'gmp_certificate',
            'manufacturing_license',
            'financial_report',
            'products_list',
            'site_master_file',
            'exclusive_agency_contract',
        ];

        $uploadedDocumentTypes = $this->documents()
            ->whereIn('document_type', $requiredDocumentTypes)
            ->pluck('document_type')
            ->toArray();

        // Check all required documents are uploaded
        $hasRequired = true;
        foreach ($requiredDocumentTypes as $type) {
            if (!in_array($type, $uploadedDocumentTypes)) {
                $hasRequired = false;
                break;
            }
        }

        // Check FDA or EMEA (at least one required)
        $hasFdaOrEmea = $this->documents()
            ->whereIn('document_type', ['fda_certificate', 'emea_certificate'])
            ->exists();

        // CPP, FSC, registration_certificates, and other are now optional
        // No need to check them for completion

        return $hasRequired && $hasFdaOrEmea;
    }


    public function getPendingInvoice()
    {
        return $this->invoices()
            ->where('status', 'pending')
            ->whereNull('receipt_path')
            ->first();
    }

    public function getInvoiceAwaitingReceiptReview()
    {
        return $this->invoices()
            ->where('status', 'pending')
            ->whereNotNull('receipt_path')
            ->where('receipt_status', 'pending')
            ->first();
    }

    public function markAsExpired(): bool
    {
        return $this->update(['status' => 'expired']);
    }

    public function renewCompany(): bool
    {
        $validityYears = (int) (Setting::where('key', 'foreign_company_validity_years')->first()?->value ?? 5);

        return $this->update([
            'status' => 'active',
            'last_renewed_at' => now(),
            'expires_at' => now()->addYears($validityYears),
        ]);
    }

    public function calculateExpiryDate()
    {
        $validityYears = (int) (Setting::where('key', 'foreign_company_validity_years')->first()?->value ?? 5);
        return now()->addYears($validityYears);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }
}
