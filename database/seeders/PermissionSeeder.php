<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'local_companies' => [
                'group_label' => 'الشركات المحلية',
                'permissions' => [
                    'view_local_companies' => 'عرض الشركات المحلية',
                    'create_local_company' => 'إنشاء شركة محلية',
                    'edit_local_company' => 'تعديل شركة محلية',
                    'delete_local_company' => 'حذف شركة محلية',
                    'approve_local_company' => 'الموافقة على شركة محلية',
                    'reject_local_company' => 'رفض شركة محلية',
                    'activate_local_company' => 'تفعيل شركة محلية',
                    'print_local_company_certificate' => 'طباعة شهادة شركة محلية',
                    'export_local_companies' => 'تصدير/طباعة تقارير الشركات المحلية',
                ],
            ],
            'foreign_companies' => [
                'group_label' => 'الشركات الأجنبية',
                'permissions' => [
                    'view_foreign_companies' => 'عرض الشركات الأجنبية',
                    'create_foreign_company' => 'إنشاء شركة أجنبية',
                    'approve_foreign_company' => 'الموافقة على شركة أجنبية',
                    'reject_foreign_company' => 'رفض شركة أجنبية',
                    'activate_foreign_company' => 'تفعيل شركة أجنبية',
                    'suspend_foreign_company' => 'تعليق/إلغاء تعليق شركة أجنبية',
                    'manage_cgmp_certificate' => 'إدارة شهادة CGMP',
                    'print_foreign_company_certificate' => 'طباعة شهادة شركة أجنبية',
                    'export_foreign_companies' => 'تصدير/طباعة تقارير الشركات الأجنبية',
                ],
            ],
            'pharmaceutical_products' => [
                'group_label' => 'الأصناف الدوائية',
                'permissions' => [
                    'view_pharmaceutical_products' => 'عرض الأصناف الدوائية',
                    'preliminary_approve_product' => 'الموافقة المبدئية على صنف',
                    'final_approve_product' => 'الموافقة النهائية على صنف',
                    'reject_product' => 'رفض صنف دوائي',
                    'approve_product_receipt' => 'الموافقة على إيصال سداد صنف',
                    'reject_product_receipt' => 'رفض إيصال سداد صنف',
                    'print_product_certificate' => 'طباعة شهادة صنف دوائي',
                ],
            ],
            'invoices' => [
                'group_label' => 'الفواتير',
                'permissions' => [
                    'view_invoices' => 'عرض الفواتير',
                    'create_invoice' => 'إنشاء فاتورة',
                    'edit_invoice' => 'تعديل فاتورة',
                    'delete_invoice' => 'حذف فاتورة',
                    'cancel_invoice' => 'إلغاء فاتورة',
                    'approve_payment_receipt' => 'الموافقة على إيصال دفع',
                    'reject_payment_receipt' => 'رفض إيصال دفع',
                ],
            ],
            'documents' => [
                'group_label' => 'المستندات',
                'permissions' => [
                    'manage_company_documents' => 'إدارة مستندات الشركات',
                    'view_company_archive' => 'عرض أرشيف مستندات الشركات',
                    'manage_admin_documents' => 'إدارة المستندات الإدارية',
                    'manage_shared_files' => 'إدارة الملفات المشتركة',
                ],
            ],
            'users' => [
                'group_label' => 'المستخدمين والأقسام',
                'permissions' => [
                    'view_users' => 'عرض المستخدمين',
                    'create_user' => 'إنشاء مستخدم',
                    'edit_user' => 'تعديل مستخدم',
                    'delete_user' => 'حذف مستخدم',
                    'toggle_user_status' => 'تفعيل/تعطيل مستخدم',
                    'manage_departments' => 'إدارة الأقسام',
                ],
            ],
            'announcements' => [
                'group_label' => 'التعميمات',
                'permissions' => [
                    'view_announcements' => 'عرض التعميمات',
                    'create_announcement' => 'إنشاء تعميم',
                    'delete_announcement' => 'حذف تعميم',
                    'send_announcement_emails' => 'إرسال التعاميم بالبريد',
                ],
            ],
            'reports' => [
                'group_label' => 'التقارير',
                'permissions' => [
                    'view_reports' => 'عرض التقارير',
                    'export_reports' => 'تصدير التقارير',
                ],
            ],
            'representatives' => [
                'group_label' => 'ممثلي الشركات',
                'permissions' => [
                    'view_representatives' => 'عرض ممثلي الشركات',
                ],
            ],
            'settings' => [
                'group_label' => 'الإعدادات',
                'permissions' => [
                    'manage_settings' => 'إدارة إعدادات النظام',
                ],
            ],
        ];

        foreach ($permissions as $group => $data) {
            foreach ($data['permissions'] as $name => $displayName) {
                Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['display_name' => $displayName, 'group' => $group]
                );
            }
        }

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['display_name' => 'مدير النظام']
        );

        $adminRole->syncPermissions(Permission::all());

        $user = User::find(1);
        if ($user) {
            $user->assignRole('admin');
        }
    }
}
