<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
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
        return DB::transaction(function () {
            $year = date('Y');
            $prefix = "PP-{$year}-";

            DB::statement("SELECT GET_LOCK('pharma_invoice_number_{$year}', 10)");

            try {
                $lastInvoice = self::where('invoice_number', 'like', "{$prefix}%")
                    ->orderByRaw('CAST(SUBSTRING(invoice_number, -4) AS UNSIGNED) DESC')
                    ->first();

                $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

                return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            } finally {
                DB::statement("SELECT RELEASE_LOCK('pharma_invoice_number_{$year}')");
            }
        });
    }
}
