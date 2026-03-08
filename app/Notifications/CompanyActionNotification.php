<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyActionNotification extends Notification
{
    use Queueable;

    public $action;
    public $companyType;
    public $companyName;
    public $companyId;
    public $representativeName;
    public $additionalData;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $action,
        string $companyType,
        string $companyName,
        int $companyId,
        string $representativeName,
        array $additionalData = []
    ) {
        $this->action = $action;
        $this->companyType = $companyType;
        $this->companyName = $companyName;
        $this->companyId = $companyId;
        $this->representativeName = $representativeName;
        $this->additionalData = $additionalData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'action' => $this->action,
            'company_type' => $this->companyType,
            'company_name' => $this->companyName,
            'company_id' => $this->companyId,
            'representative_name' => $this->representativeName,
            'additional_data' => $this->additionalData,
            'message' => $this->getMessage(),
            'url' => $this->getUrl(),
            'icon' => $this->getIcon(),
        ];
    }

    /**
     * Get notification message based on action
     */
    private function getMessage(): string
    {
        $companyTypeArabic = $this->companyType == 'local' ? 'محلية' : 'أجنبية';

        return match($this->action) {
            'company_created' => "قام {$this->representativeName} بإنشاء شركة {$companyTypeArabic} جديدة: {$this->companyName}",
            'company_updated' => "قام {$this->representativeName} بتحديث بيانات الشركة {$companyTypeArabic}: {$this->companyName}",
            'company_resubmitted' => "قام {$this->representativeName} بإعادة تقديم الشركة {$companyTypeArabic} بعد الرفض: {$this->companyName}",
            'receipt_uploaded' => "قام {$this->representativeName} برفع إيصال دفع للشركة {$companyTypeArabic}: {$this->companyName}",
            'receipt_deleted' => "قام {$this->representativeName} بحذف إيصال دفع للشركة {$companyTypeArabic}: {$this->companyName}",
            'document_uploaded' => "قام {$this->representativeName} برفع مستند للشركة {$companyTypeArabic}: {$this->companyName}",
            'document_deleted' => "قام {$this->representativeName} بحذف مستند للشركة {$companyTypeArabic}: {$this->companyName}",
            default => "إجراء جديد من {$this->representativeName} للشركة {$companyTypeArabic}: {$this->companyName}",
        };
    }

    /**
     * Get URL based on company type
     */
    private function getUrl(): string
    {
        if ($this->companyType == 'local') {
            return route('admin.local-companies.show', $this->companyId);
        } else {
            return route('admin.foreign-companies.show', $this->companyId);
        }
    }

    /**
     * Get icon based on action
     */
    private function getIcon(): string
    {
        return match($this->action) {
            'company_created' => 'ti-plus',
            'company_updated' => 'ti-edit',
            'company_resubmitted' => 'ti-refresh',
            'receipt_uploaded' => 'ti-upload',
            'receipt_deleted' => 'ti-trash',
            'document_uploaded' => 'ti-file-upload',
            'document_deleted' => 'ti-file-x',
            default => 'ti-bell',
        };
    }
}
