<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnnouncementSubmission extends Model
{
    protected $fillable = [
        'announcement_id',
        'representative_id',
        'data',
        'submitted_at',
    ];

    protected $casts = [
        'data' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function representative(): BelongsTo
    {
        return $this->belongsTo(CompanyRepresentative::class, 'representative_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(AnnouncementSubmissionFile::class, 'submission_id');
    }
}
