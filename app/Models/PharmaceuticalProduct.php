<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PharmaceuticalProduct extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('admin_menu_counts'));
        static::saved(fn () => Cache::forget('dashboard_stats'));
        static::deleted(fn () => Cache::forget('admin_menu_counts'));
        static::deleted(fn () => Cache::forget('dashboard_stats'));
    }

    protected $fillable = [
        'foreign_company_id',
        'representative_id',
        'product_name',
        'scientific_name',
        'pharmaceutical_form',
        'concentration',
        'usage_methods',
        'other_usage_method',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'trade_name',
        'origin',
        'unit',
        'packaging',
        'quantity',
        'unit_price',
        'shelf_life_months',
        'storage_conditions',
        'free_sale',
        'samples',
        'pharmacopeal_ref',
        'item_classification',
        'preliminary_approved_at',
        'preliminary_approved_by',
        'final_approved_at',
        'final_approved_by',
        'registration_number',
        'is_pre_registered',
        'pre_registration_number',
        'pre_registration_year',
    ];

    protected $casts = [
        'usage_methods' => 'array',
        'reviewed_at' => 'datetime',
        'preliminary_approved_at' => 'datetime',
        'final_approved_at' => 'datetime',
        'unit_price' => 'decimal:2',
        'is_pre_registered' => 'boolean',
    ];

    public function foreignCompany(): BelongsTo
    {
        return $this->belongsTo(ForeignCompany::class);
    }

    public function representative(): BelongsTo
    {
        return $this->belongsTo(User::class, 'representative_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function preliminaryApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'preliminary_approved_by');
    }

    public function finalApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'final_approved_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PharmaceuticalProductDocument::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(PharmaceuticalProductInvoice::class);
    }

    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'uploading_documents' => 'قيد رفع المستندات',
            'pending_review' => 'قيد المراجعة',
            'preliminary_approved' => 'موافقة مبدئية',
            'pending_final_approval' => 'قيد الموافقة النهائية',
            'pending_payment' => 'قيد السداد',
            'payment_review' => 'قيد مراجعة السداد',
            'rejected' => 'مرفوض',
            'active' => 'معتمد',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'uploading_documents' => 'bg-info',
            'pending_review' => 'bg-warning',
            'preliminary_approved' => 'bg-primary',
            'pending_final_approval' => 'bg-warning',
            'pending_payment' => 'bg-warning',
            'payment_review' => 'bg-info',
            'rejected' => 'bg-danger',
            'active' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    public function hasUnpaidInvoice(): bool
    {
        return $this->invoices()->whereIn('status', ['unpaid', 'pending_review'])->exists();
    }

    public function getUnpaidInvoice()
    {
        return $this->invoices()->whereIn('status', ['unpaid', 'pending_review'])->first();
    }

    public function getUsageMethodsTextAttribute(): string
    {
        if (empty($this->usage_methods)) {
            return '-';
        }

        $methods = [
            'oral' => 'فموي',
            'injection' => 'حقن',
            'topical' => 'موضعي',
            'inhalation' => 'استنشاق',
            'other' => 'أخرى',
        ];

        $text = collect($this->usage_methods)
            ->map(fn($method) => $methods[$method] ?? $method)
            ->join('، ');

        if (in_array('other', $this->usage_methods) && $this->other_usage_method) {
            $text .= ' (' . $this->other_usage_method . ')';
        }

        return $text;
    }

    public function hasAllRequiredDocuments(): bool
    {
        $requiredTypes = PharmaceuticalProductDocument::getRequiredDocumentTypes();
        $uploadedTypes = $this->documents()
            ->select('document_type')
            ->distinct()
            ->pluck('document_type')
            ->toArray();

        return count(array_diff($requiredTypes, $uploadedTypes)) == 0;
    }

    public function getMissingDocumentTypes(): array
    {
        $requiredTypes = PharmaceuticalProductDocument::getRequiredDocumentTypes();
        $uploadedTypes = $this->documents()->pluck('document_type')->toArray();

        return array_diff($requiredTypes, $uploadedTypes);
    }

    public function getUploadedDocumentTypes(): array
    {
        return $this->documents()->pluck('document_type')->toArray();
    }

    public static function generateRegistrationNumber(): string
    {
        return DB::transaction(function () {
            $year = date('Y');

            DB::statement("SELECT GET_LOCK('pharma_reg_number_{$year}', 10)");

            try {
                $lastProduct = static::whereNotNull('registration_number')
                    ->where('registration_number', 'like', $year . '-%')
                    ->orderByRaw("CAST(SUBSTRING_INDEX(registration_number, '-', -1) AS UNSIGNED) DESC")
                    ->first();

                $nextNumber = ($lastProduct && preg_match('/-(\d+)$/', $lastProduct->registration_number, $matches))
                    ? (int) $matches[1] + 1
                    : 1;

                return "{$year}-{$nextNumber}";
            } finally {
                DB::statement("SELECT RELEASE_LOCK('pharma_reg_number_{$year}')");
            }
        });
    }

    public function hasCompleteDetailedInfo(): bool
    {
        return !empty($this->trade_name) &&
               !empty($this->origin) &&
               !empty($this->unit) &&
               !empty($this->packaging) &&
               !empty($this->quantity) &&
               !empty($this->shelf_life_months) &&
               !empty($this->storage_conditions) &&
               !empty($this->free_sale) &&
               !empty($this->samples) &&
               !empty($this->pharmacopeal_ref) &&
               !empty($this->item_classification);
    }
}
