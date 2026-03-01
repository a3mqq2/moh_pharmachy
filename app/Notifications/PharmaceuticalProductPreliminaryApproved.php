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
            ->subject('الموافقة المبدئية على الصنف الدوائي - يرجى استكمال البيانات')
            ->view('emails.pharmaceutical-product-preliminary-approved', [
                'product' => $this->product,
                'representative' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'الموافقة المبدئية على الصنف الدوائي',
            'message' => 'تمت الموافقة المبدئية على الصنف الدوائي: ' . $this->product->product_name . '. يرجى استكمال البيانات التفصيلية.',
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'url' => route('representative.pharmaceutical-products.edit-details', $this->product->id),
            'type' => 'pharmaceutical_product_preliminary_approved',
            'icon' => 'ti-check',
        ];
    }
}
