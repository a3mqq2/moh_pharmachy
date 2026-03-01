<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LocalCompanyActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'local_company_id',
        'user_id',
        'action',
        'description',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function localCompany()
    {
        return $this->belongsTo(LocalCompany::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log(LocalCompany $company, string $action, string $description, array $properties = [])
    {
        return self::create([
            'local_company_id' => $company->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'properties' => $properties ?: null,
        ]);
    }

    public function getActionIconAttribute()
    {
        return match($this->action) {
            'created' => 'ti-plus text-success',
            'updated' => 'ti-edit text-primary',
            'approved' => 'ti-check text-success',
            'rejected' => 'ti-x text-danger',
            'document_uploaded' => 'ti-upload text-info',
            'document_deleted' => 'ti-trash text-danger',
            'status_changed' => 'ti-refresh text-warning',
            default => 'ti-activity text-secondary',
        };
    }

    public function getActionColorAttribute()
    {
        return match($this->action) {
            'created' => 'success',
            'updated' => 'primary',
            'approved' => 'success',
            'rejected' => 'danger',
            'document_uploaded' => 'info',
            'document_deleted' => 'danger',
            'status_changed' => 'warning',
            default => 'secondary',
        };
    }
}
