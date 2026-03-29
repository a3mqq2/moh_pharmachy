<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementSubmissionFile extends Model
{
    protected $fillable = [
        'submission_id',
        'field_name',
        'file_path',
        'original_name',
        'mime_type',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(AnnouncementSubmission::class, 'submission_id');
    }
}
