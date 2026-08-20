<?php

namespace App\Modules\Shipping\Exceptions;

use Exception;

/**
 * تُرمى عند:
 * - محاولة تفعيل أكثر من منطقة افتراضية بنفس الوقت (تحقّق إضافي بعد الـ transaction)
 * - عدم وجود أي طريقة شحن فعالة (active) داخل منطقة تمت مطابقتها
 * - بيانات ناقصة أساسية (بدون country_code مثلًا) لا يُسمح بالمتابعة بدونها
 */
class InvalidShippingConfigurationException extends Exception
{
}
