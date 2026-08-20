<?php

namespace Database\Seeders;

use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Database\Seeder;

/**
 * يضيف صلاحيات إدارة الشحن ويربطها بدور الأدمن.
 * يستخدم updateOrCreate — آمن للتشغيل المتكرر.
 */
class ShippingPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name'         => 'manage-shipping',
                'display_name' => 'إدارة مناطق وطرق الشحن',
                'group'        => 'shipping',
            ],
            [
                'name'         => 'view-shipping-logs',
                'display_name' => 'عرض سجلات الشحن والتغطية',
                'group'        => 'shipping',
            ],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['name' => $perm['name']], $perm);
        }

        // ربط الصلاحيات بدور الأدمن تلقائيًا
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $ids = Permission::whereIn('name', array_column($permissions, 'name'))->pluck('id');
            $adminRole->permissions()->syncWithoutDetaching($ids);
        }

        $this->command->info('✅ Shipping permissions seeded and attached to admin role.');
    }
}
