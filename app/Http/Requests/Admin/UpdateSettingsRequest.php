<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $group = $this->input('group', 'general');

        $base = [
            'group' => 'required|string',
        ];

        return match ($group) {
            'store' => array_merge($base, [
                'store_name' => 'nullable|string|max:255',
                'store_email' => 'nullable|email|max:255',
                'store_phone' => 'nullable|string|max:50',
                'store_address' => 'nullable|string|max:500',
                'store_description' => 'nullable|string|max:1000',
                'store_logo_file' => 'nullable|image|mimes:jpeg,jpg,png,webp,svg|max:2048',
                'store_favicon_file' => 'nullable|image|mimes:jpeg,jpg,png,webp,svg|max:2048',
            ]),
            'contact' => array_merge($base, [
                'contact_phone' => 'nullable|string|max:50',
                'contact_email' => 'nullable|email|max:255',
                'contact_whatsapp' => 'nullable|string|max:50',
                'contact_address' => 'nullable|string',
                'contact_working_hours' => 'nullable|string|max:255',
                'contact_support_hours' => 'nullable|string|max:255',
            ]),
            'social' => array_merge($base, [
                'social_whatsapp' => 'nullable|string|max:50',
                'social_facebook' => 'nullable|url|max:500',
                'social_instagram' => 'nullable|url|max:500',
                'social_tiktok' => 'nullable|url|max:500',
                'social_youtube' => 'nullable|url|max:500',
                'social_telegram' => 'nullable|url|max:500',
                'social_snapchat' => 'nullable|url|max:500',
            ]),
            'seo' => array_merge($base, [
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:500',
                'seo_ga_id' => 'nullable|string|max:50',
                'seo_fb_pixel' => 'nullable|string|max:50',
                'seo_og_image_file' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            ]),
            'currency' => array_merge($base, [
                'currency_symbol' => 'nullable|string|max:10',
                'currency_code' => 'nullable|string|size:3',
                'fallback_currency' => 'nullable|string|size:3',
            ]),
            'store_extended' => array_merge($base, [
                'store_wilaya' => 'nullable|string|max:255',
                'store_commune' => 'nullable|string|max:255',
                'store_postal_code' => 'nullable|string|max:20',
                'store_website' => 'nullable|string|max:500',
                'store_phone_secondary' => 'nullable|string|max:50',
            ]),
            'invoice_info' => array_merge($base, [
                'invoice_business_name' => 'nullable|string|max:255',
                'invoice_legal_name' => 'nullable|string|max:255',
                'invoice_rc' => 'nullable|string|max:100',
                'invoice_nif' => 'nullable|string|max:100',
                'invoice_nis' => 'nullable|string|max:100',
                'invoice_phone' => 'nullable|string|max:50',
                'invoice_address' => 'nullable|string|max:500',
                'invoice_email' => 'nullable|email|max:255',
                'invoice_notes' => 'nullable|string|max:2000',
            ]),
            default => $base,
        };
    }
}
