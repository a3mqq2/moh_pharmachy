<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ForeignCompanyInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'foreign_company_id',
        'invoice_number',
        'amount',
        'description',
        'status',
        'receipt_path',
        'receipt_uploaded_at',
        'receipt_status',
        'receipt_rejection_reason',
        'receipt_reviewed_by',
        'receipt_reviewed_at',
        'paid_at',
        'issued_by',
        'approved_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'receipt_uploaded_at' => 'datetime',
        'receipt_reviewed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // Relations
    public function foreignCompany(): BelongsTo
    {
        return $this->belongsTo(ForeignCompany::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receiptReviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receipt_reviewed_by');
    }

    // Accessors
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'pending' => 'قيد الانتظار',
            'paid' => 'مدفوعة',
            'cancelled' => 'ملغاة',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'paid' => 'badge-success',
            'cancelled' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    public function getReceiptStatusNameAttribute(): ?string
    {
        if (!$this->receipt_status) {
            return null;
        }

        return match($this->receipt_status) {
            'pending' => 'قيد المراجعة',
            'approved' => 'مقبول',
            'rejected' => 'مرفوض',
            default => $this->receipt_status,
        };
    }

    public function getReceiptStatusBadgeClassAttribute(): ?string
    {
        if (!$this->receipt_status) {
            return null;
        }

        return match($this->receipt_status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    // Methods
    public function hasReceipt(): bool
    {
        return !empty($this->receipt_path) && Storage::disk('public')->exists($this->receipt_path);
    }

    public function approveReceipt(?int $reviewedBy = null): bool
    {
        $updated = $this->update([
            'receipt_status' => 'approved',
            'receipt_rejection_reason' => null,
            'receipt_reviewed_by' => $reviewedBy ?? (auth()->check() ? auth()->id() : null),
            'receipt_reviewed_at' => now(),
        ]);

        if ($updated) {
            // Mark invoice as paid
            $this->markAsPaid();
        }

        return $updated;
    }

    public function rejectReceipt(string $reason, ?int $reviewedBy = null): bool
    {
        return $this->update([
            'receipt_status' => 'rejected',
            'receipt_rejection_reason' => $reason,
            'receipt_reviewed_by' => $reviewedBy ?? (auth()->check() ? auth()->id() : null),
            'receipt_reviewed_at' => now(),
        ]);
    }

    public function markAsPaid(): bool
    {
        return $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'approved_by' => auth()->check() ? auth()->id() : null,
        ]);
    }

    public function markAsUnpaid(): bool
    {
        // Delete receipt file if exists
        if ($this->receipt_path && Storage::disk('public')->exists($this->receipt_path)) {
            Storage::disk('public')->delete($this->receipt_path);
        }

        return $this->update([
            'receipt_path' => null,
            'receipt_uploaded_at' => null,
            'receipt_status' => null,
            'receipt_rejection_reason' => null,
            'receipt_reviewed_by' => null,
            'receipt_reviewed_at' => null,
        ]);
    }

    public function canUploadReceipt(): bool
    {
        return $this->status === 'pending' && (empty($this->receipt_path) || $this->receipt_status === 'rejected');
    }

    public function canDeleteReceipt(): bool
    {
        return $this->status === 'pending' && !empty($this->receipt_path) && $this->receipt_status !== 'approved';
    }

    public function getReceiptDownloadUrl(): ?string
    {
        if (!$this->hasReceipt()) {
            return null;
        }

        return route('representative.foreign-companies.invoices.download-receipt', [
            'company' => $this->foreign_company_id,
            'invoice' => $this->id
        ]);
    }

    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $lastInvoice = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -6));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'FC-' . $year . '-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
