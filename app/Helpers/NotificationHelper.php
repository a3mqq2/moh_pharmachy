<?php

namespace App\Helpers;

use App\Models\User;
use App\Notifications\CompanyActionNotification;

class NotificationHelper
{
    /**
     * Send notification to all admin users
     */
    public static function notifyAdmins(
        string $action,
        string $companyType,
        string $companyName,
        int $companyId,
        string $representativeName,
        array $additionalData = []
    ): void {
        // Get all active admin users
        $admins = User::where('is_active', true)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->get();

        // Send notification to each admin
        foreach ($admins as $admin) {
            $admin->notify(new CompanyActionNotification(
                $action,
                $companyType,
                $companyName,
                $companyId,
                $representativeName,
                $additionalData
            ));
        }
    }
}
