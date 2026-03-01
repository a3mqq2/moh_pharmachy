<?php

namespace App\Notifications;

use App\Models\PharmaceuticalProduct;
use App\Models\PharmaceuticalProductInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PharmaceuticalProductFinalApproved extends Notification
{
    use Queueable;

    public function __construct(
        public PharmaceuticalProduct $product,
        public PharmaceuticalProductInvoice $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('الموافقة النهائية على الصنف الدوائي - يرجى سداد الفاتورة')
            ->view('emails.pharmaceutical-product-final-approved', [
                'product' => $this->product,
                'invoice' => $this->invoice,
                'representative' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'الموافقة النهائية على الصنف الدوائي',
            'message' => 'تمت الموافقة النهائية على الصنف الدوائي: ' . $this->product->product_name . '. يرجى سداد الفاتورة.',
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->amount,
            'url' => route('representative.pharmaceutical-products.show', $this->product->id),
            'type' => 'pharmaceutical_product_final_approved',
            'icon' => 'ti-check-circle',
        ];
    }
}
