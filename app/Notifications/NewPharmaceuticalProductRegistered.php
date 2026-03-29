<?php

namespace App\Notifications;

use App\Models\PharmaceuticalProduct;
use App\Models\CompanyRepresentative;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPharmaceuticalProductRegistered extends Notification
{
    use Queueable;

    public function __construct(
        public PharmaceuticalProduct $product,
        public CompanyRepresentative $representative
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('notifications.product_registered_title'),
            'message' => __('notifications.product_registered_message', ['product' => $this->product->product_name]),
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'representative_name' => $this->representative->name,
            'foreign_company_name' => $this->product->foreignCompany->company_name,
            'url' => route('admin.pharmaceutical-products.show', $this->product->id),
            'type' => 'pharmaceutical_product_registered',
        ];
    }
}
