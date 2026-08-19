<?php

namespace App\Console\Commands;

use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature = 'ecommerce:create-admin
                            {--name= : اسم المدير}
                            {--email= : البريد الإلكتروني}
                            {--phone= : رقم الهاتف}
                            {--password= : كلمة المرور (إذا لم تُمرر ستُسأل)}
                            {--country=SD : رمز الدولة (SD, DZ, MA, TN, LY, EG)}
                            {--role=admin : الدور (admin أو manager)}';

    protected $description = 'إنشاء حساب مدير نظام أو مدير متجر جديد';

    public function handle(): int
    {
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║   إنشاء حساب مدير جديد - Amar Store   ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();

        // Gather data interactively or from options
        $name = $this->option('name') ?: $this->ask('الاسم الكامل');
        $email = $this->option('email') ?: $this->ask('البريد الإلكتروني');
        $phone = $this->option('phone') ?: $this->ask('رقم الهاتف (مثال: 0912345678)');
        $password = $this->option('password') ?: $this->secret('كلمة المرور (6 أحرف على الأقل)');
        $country = strtoupper($this->option('country') ?: $this->ask('رمز الدولة (DZ)', 'DZ'));
        $roleName = $this->option('role') ?: $this->choice('الدور', ['admin', 'manager'], 0);

        // Validate
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'country_code' => $country,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'country_code' => 'required|string|size:2',
        ], [
            'email.unique' => 'هذا البريد مسجل لمستخدم آخر',
            'phone.unique' => 'هذا الرقم مسجل لمستخدم آخر',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
        ]);

        if ($validator->fails()) {
            $this->error('أخطاء في المدخلات:');
            foreach ($validator->errors()->all() as $error) {
                $this->line('  - '.$error);
            }

            return self::FAILURE;
        }

        // Check role exists
        $role = Role::where('name', $roleName)->first();
        if (! $role) {
            $this->error("الدور '{$roleName}' غير موجود. شغل: php artisan db:seed أولاً");

            return self::FAILURE;
        }

        // Add country dial code
        $countries = config('ecommerce.countries', []);
        $dial = $countries[$country]['dial_code'] ?? '';
        $fullPhone = str_starts_with($phone, '+') ? $phone : ($dial.$phone);

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $fullPhone,
            'country_code' => $country,
            'password' => Hash::make($password),
            'role' => $roleName,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $user->roles()->attach($role);

        $this->newLine();
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║         تم إنشاء الحساب بنجاح         ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->table(
            ['الحقل', 'القيمة'],
            [
                ['الاسم', $user->name],
                ['البريد', $user->email],
                ['الهاتف', $user->phone],
                ['الدور', $role->display_name],
                ['الحالة', 'نشط'],
            ]
        );

        $this->newLine();
        $this->comment('يمكنك الآن تسجيل الدخول عبر:');
        $this->line('  - لوحة الإدارة: '.url('/admin'));
        $this->line('  - البريد: '.$email);
        $this->line('  - كلمة المرور: [التي أدخلتها]');

        return self::SUCCESS;
    }
}
