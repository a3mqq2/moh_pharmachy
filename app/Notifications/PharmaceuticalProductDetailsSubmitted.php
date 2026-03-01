<?php

namespace App\Notifications;

use App\Models\PharmaceuticalProduct;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PharmaceuticalProductDetailsSubmitted extends Notification
{
    use Queueable;

    public function __construct(
        public PharmaceuticalProduct $product,
        public User $representative
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'تم إرسال بيانات تفصيلية لصنف دوائي',
            'message' => 'قام ' . $this->representative->name . ' بإرسال البيانات التفصيلية للصنف الدوائي: ' . $this->product->product_name,
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'representative_name' => $this->representative->name,
            'url' => route('admin.pharmaceutical-products.show', $this->product->id),
            'type' => 'pharmaceutical_product_details_submitted',
            'icon' => 'ti-send',
        ];
    }
}
