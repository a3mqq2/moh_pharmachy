<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PharmaceuticalProductInvoice extends Model
{
    protected $fillable = [
        'pharmaceutical_product_id',
        'invoice_number',
        'amount',
        'status',
        'receipt_path',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function pharmaceuticalProduct(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalProduct::class);
    }

    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'unpaid' => 'غير مدفوعة',
            'pending_review' => 'قيد المراجعة',
            'paid' => 'مدفوعة',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'unpaid' => 'danger',
            'pending_review' => 'warning',
            'paid' => 'success',
            default => 'secondary',
        };
    }

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_path ? Storage::url($this->receipt_path) : null;
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $lastInvoice = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastInvoice) {
            $sequence = 1;
        } else {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $sequence = $lastNumber + 1;
        }

        return 'INV-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
