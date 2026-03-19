<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'priority',
        'target',
        'start_date',
        'end_date',
        'send_email',
        'is_sent',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'send_email' => 'boolean',
        'is_sent' => 'boolean',
        'sent_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function getIsActiveAttribute(): bool
    {
        $now = now()->startOfDay();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    public function getStatusLabelAttribute(): string
    {
        $now = now()->startOfDay();

        if ($this->start_date && $now->lt($this->start_date)) {
            return 'مجدول';
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return 'منتهي';
        }

        return 'ساري';
    }

    public function getStatusColorAttribute(): string
    {
        $now = now()->startOfDay();

        if ($this->start_date && $now->lt($this->start_date)) {
            return 'info';
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return 'secondary';
        }

        return 'success';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPriorityNameAttribute(): string
    {
        return match($this->priority) {
            'normal' => 'عادي',
            'important' => 'مهم',
            'urgent' => 'عاجل',
            default => $this->priority,
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'normal' => 'info',
            'important' => 'warning',
            'urgent' => 'danger',
            default => 'secondary',
        };
    }

    public function getTargetNameAttribute(): string
    {
        return match($this->target) {
            'all' => 'جميع الشركات',
            'local' => 'الشركات المحلية',
            'foreign' => 'الشركات الأجنبية',
            default => $this->target,
        };
    }
}
