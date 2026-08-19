<?php

namespace Database\Seeders;

use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Database\Seeder;

class DocumentPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'manage_store_settings',   'display_name' => 'إدارة إعدادات المتجر',      'group' => 'settings'],
            ['name' => 'manage_footer',           'display_name' => 'إدارة فوتر المتجر',          'group' => 'settings'],
            ['name' => 'manage_invoice_templates','display_name' => 'إدارة قوالب الفواتير',       'group' => 'documents'],
            ['name' => 'manage_label_templates',  'display_name' => 'إدارة قوالب ملصقات الطلبات',  'group' => 'documents'],
            ['name' => 'print_invoices',          'display_name' => 'طباعة الفواتير',             'group' => 'documents'],
            ['name' => 'print_labels',            'display_name' => 'طباعة ملصقات الطلبات',       'group' => 'documents'],
            ['name' => 'download_invoices',       'display_name' => 'تحميل فواتير PDF',           'group' => 'documents'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['name' => $perm['name']], $perm);
        }

        // Attach all to admin role if role exists
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $allPermIds = Permission::whereIn('name', array_column($permissions, 'name'))->pluck('id');
            $adminRole->permissions()->syncWithoutDetaching($allPermIds);
        }
    }
}
