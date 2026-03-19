<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LocalCompany extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('admin_menu_counts'));
        static::saved(fn () => Cache::forget('dashboard_stats'));
        static::deleted(fn () => Cache::forget('admin_menu_counts'));
        static::deleted(fn () => Cache::forget('dashboard_stats'));
    }

    protected $fillable = [
        'company_name',
        'company_type',
        'company_address',
        'latitude',
        'longitude',
        'street',
        'city',
        'phone',
        'mobile',
        'email',
        'registration_date',
        'registration_number',
        'license_type',
        'license_specialty',
        'license_number',
        'license_issuer',
        'food_drug_registration_number',
        'chamber_of_commerce_number',
        'manager_name',
        'manager_position',
        'manager_phone',
        'manager_email',
        'status',
        'rejection_reason',
        'suspension_reason',
        'user_id',
        'representative_id',
        'is_pre_registered',
        'pre_registration_number',
        'pre_registration_year',
        'last_renewal_date',
        'expires_at',
        'last_renewed_at',
        'activated_at',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'activated_at' => 'datetime',
        'last_renewal_date' => 'date',
        'expires_at' => 'datetime',
        'last_renewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function representative()
    {
        return $this->belongsTo(CompanyRepresentative::class, 'representative_id');
    }

    public function documents()
    {
        return $this->hasMany(LocalCompanyDocument::class);
    }

    public function activities()
    {
        return $this->hasMany(LocalCompanyActivity::class)->orderBy('created_at', 'desc');
    }

    public function invoices()
    {
        return $this->hasMany(LocalCompanyInvoice::class)->orderBy('created_at', 'desc');
    }

    public function hasUnpaidInvoices()
    {
        return $this->invoices()->where('status', 'unpaid')->exists();
    }

    public function getUnpaidInvoicesTotal()
    {
        return $this->invoices()->where('status', 'unpaid')->sum('amount');
    }

    public function logActivity(string $action, string $description, array $properties = [])
    {
        return LocalCompanyActivity::log($this, $action, $description, $properties);
    }

    public static function generateRegistrationNumber()
    {
        return DB::transaction(function () {
            $currentYear = date('Y');

            DB::statement("SELECT GET_LOCK('local_reg_number_{$currentYear}', 10)");

            try {
                $lastCompany = self::whereNotNull('registration_number')
                    ->where('registration_number', 'like', $currentYear . '-%')
                    ->orderByRaw("CAST(SUBSTRING_INDEX(registration_number, '-', -1) AS UNSIGNED) DESC")
                    ->first();

                $nextSequence = ($lastCompany && preg_match('/-(\d+)$/', $lastCompany->registration_number, $matches))
                    ? (int) $matches[1] + 1
                    : 1;

                return $currentYear . '-' . $nextSequence;
            } finally {
                DB::statement("SELECT RELEASE_LOCK('local_reg_number_{$currentYear}')");
            }
        });
    }

    public static function licenseTypes()
    {
        return [
            'company' => 'شركة',
            'partnership' => 'تشاركية',
            'authorized_agent' => 'وكيل معتمد',
        ];
    }

    public static function licenseSpecialties()
    {
        return [
            'medicines' => 'أدوية',
            'medical_supplies' => 'مستلزمات طبية',
            'medical_equipment' => 'معدات طبية',
        ];
    }

    public static function statuses()
    {
        return [
            'uploading_documents' => 'قيد رفع المستندات',
            'pending' => 'قيد المراجعة',
            'approved' => 'مقبولة (قيد السداد)',
            'payment_review' => 'قيد مراجعة الدفع',
            'active' => 'مفعلة',
            'rejected' => 'مرفوضة',
            'suspended' => 'معلقة',
            'expired' => 'منتهية الصلاحية',
        ];
    }

    public static function companyTypes()
    {
        return [
            'distributor' => 'شركة موزعة',
            'supplier' => 'شركة موردة',
        ];
    }

    public function getLicenseTypeNameAttribute()
    {
        return self::licenseTypes()[$this->license_type] ?? $this->license_type;
    }

    public function getLicenseSpecialtyNameAttribute()
    {
        return self::licenseSpecialties()[$this->license_specialty] ?? $this->license_specialty;
    }

    public function getCompanyTypeNameAttribute()
    {
        return self::companyTypes()[$this->company_type] ?? $this->company_type;
    }

    public function getStatusNameAttribute()
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'uploading_documents' => 'info',
            'pending' => 'warning',
            'approved' => 'primary',
            'payment_review' => 'warning',
            'active' => 'success',
            'rejected' => 'danger',
            'suspended' => 'secondary',
            'expired' => 'danger',
            default => 'primary',
        };
    }

    public function getMissingDocuments()
    {
        $requiredTypes = LocalCompanyDocument::requiredDocumentTypes();
        $uploadedTypes = $this->documents->pluck('document_type')->toArray();
        $missing = [];

        foreach ($requiredTypes as $type) {
            if (!in_array($type, $uploadedTypes)) {
                $missing[$type] = LocalCompanyDocument::documentTypes()[$type];
            }
        }

        return $missing;
    }

    public function hasAllRequiredDocuments()
    {
        return count($this->getMissingDocuments()) == 0;
    }

    public function markAsExpired()
    {
        $this->update(['status' => 'expired']);
        $this->logActivity('expired', 'انتهت صلاحية الشركة');
    }

    public function renewCompany()
    {
        $validityYears = $this->company_type === 'distributor' ? 5 : 1;
        $baseDate = ($this->expires_at && $this->expires_at->isFuture()) ? $this->expires_at : now();

        $this->update([
            'status' => 'active',
            'last_renewed_at' => now(),
            'expires_at' => \Carbon\Carbon::parse($baseDate)->addYears($validityYears),
        ]);

        $this->logActivity('renewed', 'تم تجديد صلاحية الشركة لمدة ' . $validityYears . ' سنة');
    }

    public function calculateExpiryDate()
    {
        $validityYears = $this->company_type === 'distributor' ? 5 : 1;
        return now()->addYears($validityYears);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
