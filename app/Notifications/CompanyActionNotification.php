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

    public function via(object $notifiable): array
    {
        return ['database'];
    }

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

    private function getMessage(): string
    {
        $companyTypeLabel = $this->companyType == 'local'
            ? __('notifications.company_type_local')
            : __('notifications.company_type_foreign');

        $params = [
            'representative' => $this->representativeName,
            'type' => $companyTypeLabel,
            'company' => $this->companyName,
        ];

        return match($this->action) {
            'company_created' => __('notifications.company_created', $params),
            'company_updated' => __('notifications.company_updated', $params),
            'company_resubmitted' => __('notifications.company_resubmitted', $params),
            'receipt_uploaded' => __('notifications.receipt_uploaded', $params),
            'receipt_deleted' => __('notifications.receipt_deleted', $params),
            'document_uploaded' => __('notifications.document_uploaded', $params),
            'document_updated' => __('notifications.document_updated', $params),
            'document_deleted' => __('notifications.document_deleted', $params),
            default => __('notifications.company_action_default', $params),
        };
    }

    private function getUrl(): string
    {
        if ($this->companyType == 'local') {
            return route('admin.local-companies.show', $this->companyId);
        } else {
            return route('admin.foreign-companies.show', $this->companyId);
        }
    }

    private function getIcon(): string
    {
        return match($this->action) {
            'company_created' => 'ti-plus',
            'company_updated' => 'ti-edit',
            'company_resubmitted' => 'ti-refresh',
            'receipt_uploaded' => 'ti-upload',
            'receipt_deleted' => 'ti-trash',
            'document_uploaded' => 'ti-file-upload',
            'document_updated' => 'ti-file-alert',
            'document_deleted' => 'ti-file-x',
            default => 'ti-bell',
        };
    }
}
