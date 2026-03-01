<?php

namespace App\Notifications;

use App\Models\PharmaceuticalProduct;
use App\Models\CompanyRepresentative;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PharmaceuticalProductSubmittedForReview extends Notification
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
            'title' => 'طلب مراجعة صنف دوائي',
            'message' => 'تم إرسال طلب مراجعة الصنف الدوائي: ' . $this->product->product_name,
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'representative_name' => $this->representative->name,
            'foreign_company_name' => $this->product->foreignCompany->company_name,
            'url' => route('admin.pharmaceutical-products.show', $this->product->id),
            'type' => 'pharmaceutical_product_submitted',
            'icon' => 'ti-pill',
        ];
    }
}
