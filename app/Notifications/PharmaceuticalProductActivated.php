<?php

namespace App\Notifications;

use App\Models\PharmaceuticalProduct;
use App\Models\PharmaceuticalProductInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PharmaceuticalProductActivated extends Notification
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
            ->subject('تم تفعيل الصنف الدوائي')
            ->view('emails.pharmaceutical-product-activated', [
                'product' => $this->product,
                'invoice' => $this->invoice,
                'representative' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'تم تفعيل الصنف الدوائي',
            'message' => 'تم تفعيل الصنف الدوائي: ' . $this->product->product_name,
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'url' => route('representative.pharmaceutical-products.show', $this->product->id),
            'type' => 'pharmaceutical_product_activated',
            'icon' => 'ti-circle-check',
        ];
    }
}
