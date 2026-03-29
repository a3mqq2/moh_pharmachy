<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class DocumentUpdateRequest extends Model
{
    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'representative_id',
        'new_file_path',
        'original_name',
        'file_size',
        'file_extension',
        'reason',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function representative(): BelongsTo
    {
        return $this->belongsTo(CompanyRepresentative::class, 'representative_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function approve($adminId): void
    {
        $document = $this->documentable;
        $oldFilePath = $document->file_path;

        $document->file_path = $this->new_file_path;
        $document->file_size = $this->file_size;

        if ($document instanceof LocalCompanyDocument) {
            $document->original_name = $this->original_name;
            $document->file_extension = $this->file_extension;
        } elseif ($document instanceof ForeignCompanyDocument) {
            $document->document_name = $this->original_name;
            $document->mime_type = Storage::disk('public')->mimeType($this->new_file_path);
        } elseif ($document instanceof PharmaceuticalProductDocument) {
            $document->original_name = $this->original_name;
        }

        $document->save();

        if ($oldFilePath && Storage::disk('public')->exists($oldFilePath)) {
            Storage::disk('public')->delete($oldFilePath);
        }

        $this->update([
            'status' => 'approved',
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
    }

    public function reject($adminId, $reason = null): void
    {
        if ($this->new_file_path && Storage::disk('public')->exists($this->new_file_path)) {
            Storage::disk('public')->delete($this->new_file_path);
        }

        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
    }

    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            'pending' => __('invoices.status_pending'),
            'approved' => __('documents.status_approved'),
            'rejected' => __('documents.status_rejected'),
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}
