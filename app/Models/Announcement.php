<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'type',
        'form_fields',
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
        'form_fields' => 'array',
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
            return __('announcements.status_scheduled');
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return __('announcements.status_expired');
        }

        return __('announcements.status_active');
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

    public function submissions(): HasMany
    {
        return $this->hasMany(AnnouncementSubmission::class);
    }

    public function isForm(): bool
    {
        return $this->type === 'form';
    }

    public function getPriorityNameAttribute(): string
    {
        return match($this->priority) {
            'normal' => __('announcements.priority_normal'),
            'important' => __('announcements.priority_important'),
            'urgent' => __('announcements.priority_urgent'),
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
            'all' => __('announcements.target_all'),
            'local' => __('announcements.target_local'),
            'foreign' => __('announcements.target_foreign'),
            default => $this->target,
        };
    }
}
