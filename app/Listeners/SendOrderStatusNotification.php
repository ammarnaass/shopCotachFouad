<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\Content\Notification;
use App\Models\User\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $status = $event->newStatus;

        $statusMessages = [
            'pending' => 'طلبك قيد الانتظار',
            'confirmed' => 'تم تأكيد طلبك بنجاح',
            'processing' => 'جاري تجهيز طلبك',
            'shipped' => 'تم شحن طلبك وهو في الطريق إليك',
            'delivered' => 'تم تسليم طلبك بنجاح',
            'cancelled' => 'تم إلغاء طلبك',
        ];

        $message = $statusMessages[$status] ?? "تم تحديث حالة طلبك إلى: {$status}";

        // Create in-app notification for the user
        if ($order->user_id) {
            $notification = Notification::create([
                'user_id' => $order->user_id,
                'type' => 'order_status',
                'title' => 'تحديث حالة الطلب',
                'message' => $message,
                'data' => json_encode([
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $status,
                ]),
            ]);

            // Send Email notification
            $this->sendEmail($order, $status, $message);
        }
    }

    private function sendEmail($order, $status, $message): void
    {
        try {
            $email = $order->user?->email ?? $order->guest_email;
            $name = $order->user?->name ?? $order->shippingAddress?->name ?? 'عميلنا العزيز';

            if (! $email) {
                Log::warning('No email found for order', ['order_id' => $order->id]);

                return;
            }

            $subject = "تحديث حالة طلبك #{$order->order_number}";

            // Simple email content
            $content = $this->buildEmailContent($name, $order, $status, $message);

            Mail::raw($content, function ($mail) use ($email, $name, $subject) {
                $mail->to($email, $name)
                    ->subject($subject);
            });

            Log::info('Order status email sent', ['order_id' => $order->id, 'email' => $email]);
        } catch (\Exception $e) {
            Log::error('Failed to send order status email', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function buildEmailContent($name, $order, $status, $message): string
    {
        $statusLabels = [
            'pending' => 'قيد الانتظار',
            'confirmed' => 'مؤكد',
            'processing' => 'قيد التجهيز',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التسليم',
            'cancelled' => 'ملغي',
        ];

        $statusLabel = $statusLabels[$status] ?? $status;
        $storeName = config('app.name', 'متجرنا');

        return <<<EMAIL
مرحباً {$name},

{$message}

تفاصيل الطلب:
- رقم الطلب: {$order->order_number}
- الحالة الجديدة: {$statusLabel}
- المبلغ الإجمالي: {$order->grand_total} {config('ecommerce.store.currency_symbol', 'ر.س')}
- تاريخ الطلب: {$order->created_at->format('Y-m-d H:i')}

يمكنك تتبع طلبك عبر الموقع أو التواصل معنا لأي استفسار.

شكراً لتعاملكم معنا،
فريق {$storeName}
EMAIL;
    }
}
