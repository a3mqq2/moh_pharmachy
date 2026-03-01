<?php

namespace App\Notifications;

use App\Models\PharmaceuticalProduct;
use App\Models\PharmaceuticalProductInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PharmaceuticalProductReceiptRejected extends Notification
{
    use Queueable;

    public function __construct(
        public PharmaceuticalProduct $product,
        public PharmaceuticalProductInvoice $invoice,
        public string $rejectionReason
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تم رفض إيصال الدفع')
            ->view('emails.pharmaceutical-product-receipt-rejected', [
                'product' => $this->product,
                'invoice' => $this->invoice,
                'representative' => $notifiable,
                'rejectionReason' => $this->rejectionReason,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'تم رفض إيصال الدفع',
            'message' => 'تم رفض إيصال الدفع للصنف الدوائي: ' . $this->product->product_name,
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'rejection_reason' => $this->rejectionReason,
            'url' => route('representative.pharmaceutical-products.show', $this->product->id),
            'type' => 'pharmaceutical_product_receipt_rejected',
            'icon' => 'ti-x-circle',
        ];
    }
}
