<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LocalCompanyInvoice extends Model
{
    protected $fillable = [
        'local_company_id',
        'invoice_number',
        'type',
        'description',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'receipt_path',
        'receipt_uploaded_at',
        'receipt_status',
        'receipt_rejection_reason',
        'receipt_reviewed_by',
        'receipt_reviewed_at',
        'notes',
        'created_by',
        'paid_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'date',
        'receipt_uploaded_at' => 'datetime',
        'receipt_reviewed_at' => 'datetime',
    ];

    public function localCompany()
    {
        return $this->belongsTo(LocalCompany::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paidByUser()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function receiptReviewedBy()
    {
        return $this->belongsTo(User::class, 'receipt_reviewed_by');
    }

    public static function generateInvoiceNumber()
    {
        $year = date('Y');
        $lastInvoice = self::whereYear('created_at', $year)
            ->orderByRaw('CAST(SUBSTRING(invoice_number, -4) AS UNSIGNED) DESC')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'INV-' . $year . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function invoiceTypes()
    {
        return [
            'registration' => 'رسوم تسجيل',
            'renewal' => 'رسوم تجديد',
            'other' => 'أخرى',
        ];
    }

    public function getTypeNameAttribute()
    {
        return self::invoiceTypes()[$this->type] ?? $this->type;
    }

    public function getStatusNameAttribute()
    {
        return match($this->status) {
            'unpaid' => 'غير مدفوعة',
            'pending_review' => 'قيد المراجعة',
            'paid' => 'مدفوعة',
            'rejected' => 'مرفوضة',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'unpaid' => 'danger',
            'pending_review' => 'warning',
            'paid' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function getReceiptStatusNameAttribute()
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

    public function getReceiptStatusColorAttribute()
    {
        if (!$this->receipt_status) {
            return null;
        }

        return match($this->receipt_status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function isPaid()
    {
        return $this->status == 'paid';
    }

    public function markAsPaid($receiptPath = null)
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by' => auth()->id(),
            'receipt_path' => $receiptPath ?? $this->receipt_path,
        ]);

        $this->localCompany->logActivity('invoice_paid', 'تم دفع الفاتورة رقم: ' . $this->invoice_number);
    }

    public function markAsUnpaid()
    {
        if ($this->receipt_path) {
            Storage::disk('public')->delete($this->receipt_path);
        }

        $this->update([
            'status' => 'unpaid',
            'paid_at' => null,
            'paid_by' => null,
            'receipt_path' => null,
        ]);

        $this->localCompany->logActivity('invoice_unpaid', 'تم إلغاء دفع الفاتورة رقم: ' . $this->invoice_number);
    }

    public function hasReceipt()
    {
        return !empty($this->receipt_path) && Storage::disk('public')->exists($this->receipt_path);
    }

    public function approveReceipt($reviewedBy = null)
    {
        $updated = $this->update([
            'receipt_status' => 'approved',
            'receipt_rejection_reason' => null,
            'receipt_reviewed_by' => $reviewedBy ?? auth()->id(),
            'receipt_reviewed_at' => now(),
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by' => $reviewedBy ?? auth()->id(),
        ]);

        if ($updated) {
            $this->localCompany->logActivity(
                'invoice_receipt_approved',
                'تم قبول إيصال الدفع للفاتورة رقم: ' . $this->invoice_number
            );
        }

        return $updated;
    }

    public function rejectReceipt($reason, $reviewedBy = null)
    {
        $updated = $this->update([
            'receipt_status' => 'rejected',
            'receipt_rejection_reason' => $reason,
            'receipt_reviewed_by' => $reviewedBy ?? auth()->id(),
            'receipt_reviewed_at' => now(),
            'status' => 'rejected',
        ]);

        if ($updated) {
            $this->localCompany->logActivity(
                'invoice_receipt_rejected',
                'تم رفض إيصال الدفع للفاتورة رقم: ' . $this->invoice_number . ' - السبب: ' . $reason
            );
        }

        return $updated;
    }

    public function canUploadReceipt()
    {
        return in_array($this->status, ['unpaid', 'rejected']) && (empty($this->receipt_path) || $this->receipt_status == 'rejected');
    }

    public function canDeleteReceipt()
    {
        return in_array($this->status, ['unpaid', 'pending_review', 'rejected']) && !empty($this->receipt_path) && $this->receipt_status != 'approved';
    }
}
