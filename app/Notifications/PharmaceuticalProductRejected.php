<?php

namespace App\Notifications;

use App\Models\PharmaceuticalProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PharmaceuticalProductRejected extends Notification
{
    use Queueable;

    public function __construct(
        public PharmaceuticalProduct $product,
        public string $rejectionReason
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('رفض الصنف الدوائي')
            ->view('emails.pharmaceutical-product-rejected', [
                'product' => $this->product,
                'representative' => $notifiable,
                'rejectionReason' => $this->rejectionReason,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'تم رفض الصنف الدوائي',
            'message' => 'تم رفض الصنف الدوائي: ' . $this->product->product_name,
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'rejection_reason' => $this->rejectionReason,
            'url' => route('representative.pharmaceutical-products.show', $this->product->id),
            'type' => 'pharmaceutical_product_rejected',
            'icon' => 'ti-close',
        ];
    }
}
