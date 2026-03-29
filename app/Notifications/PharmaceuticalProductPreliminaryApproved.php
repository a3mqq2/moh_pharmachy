<?php

namespace App\Notifications;

use App\Models\PharmaceuticalProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PharmaceuticalProductPreliminaryApproved extends Notification
{
    use Queueable;

    public function __construct(
        public PharmaceuticalProduct $product
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.product_preliminary_approved_subject'))
            ->view('emails.pharmaceutical-product-preliminary-approved', [
                'product' => $this->product,
                'representative' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('notifications.product_preliminary_approved_title'),
            'message' => __('notifications.product_preliminary_approved_message', ['product' => $this->product->product_name]),
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'url' => route('representative.pharmaceutical-products.edit-details', $this->product->id),
            'type' => 'pharmaceutical_product_preliminary_approved',
            'icon' => 'ti-check',
        ];
    }
}
