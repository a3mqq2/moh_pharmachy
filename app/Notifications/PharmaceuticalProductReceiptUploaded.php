<?php

namespace App\Notifications;

use App\Models\PharmaceuticalProduct;
use App\Models\PharmaceuticalProductInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PharmaceuticalProductReceiptUploaded extends Notification
{
    use Queueable;

    public function __construct(
        public PharmaceuticalProduct $product,
        public PharmaceuticalProductInvoice $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'تم رفع إيصال دفع',
            'message' => 'تم رفع إيصال دفع للصنف الدوائي: ' . $this->product->product_name,
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'url' => route('admin.pharmaceutical-products.show', $this->product->id),
            'type' => 'pharmaceutical_product_receipt_uploaded',
            'icon' => 'ti-receipt',
        ];
    }
}
