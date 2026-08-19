<?php

namespace App\Models\InstantBuy;

use Illuminate\Database\Eloquent\Model;

class InstantBuySetting extends Model
{
    protected $table = 'instant_buy_settings';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'form_border_width' => 'integer',
            'form_border_radius' => 'integer',
            'section_title_size' => 'integer',
            'input_border_radius' => 'integer',
            'input_height' => 'integer',
            'button_text_size' => 'integer',
            'button_border_radius' => 'integer',
            'button_height' => 'integer',
            'summary_total_size' => 'integer',
            'trust_message_size' => 'integer',
            'success_icon_size' => 'integer',
            'success_order_number_size' => 'integer',
            'field_first_name_enabled' => 'boolean',
            'field_first_name_required' => 'boolean',
            'field_last_name_enabled' => 'boolean',
            'field_last_name_required' => 'boolean',
            'field_phone_enabled' => 'boolean',
            'field_phone_required' => 'boolean',
            'field_country_enabled' => 'boolean',
            'field_country_required' => 'boolean',
            'field_state_enabled' => 'boolean',
            'field_state_required' => 'boolean',
            'field_city_enabled' => 'boolean',
            'field_city_required' => 'boolean',
            'field_address_enabled' => 'boolean',
            'field_address_required' => 'boolean',
            'field_notes_enabled' => 'boolean',
            'field_notes_required' => 'boolean',
            'field_coupon_enabled' => 'boolean',
            'field_coupon_required' => 'boolean',
            'field_email_enabled' => 'boolean',
            'field_email_required' => 'boolean',
            'field_district_enabled' => 'boolean',
            'field_district_required' => 'boolean',
            'field_zip_enabled' => 'boolean',
            'field_zip_required' => 'boolean',
            'show_bank_transfer' => 'boolean',
            'show_product_summary' => 'boolean',
            'show_quantity_selector' => 'boolean',
            'show_price_breakdown' => 'boolean',
            'show_shipping_calculator' => 'boolean',
            'auto_select_cheapest_shipping' => 'boolean',
            'success_show_order_number' => 'boolean',
            'success_show_whatsapp_button' => 'boolean',
            'success_show_order_details' => 'boolean',
        ];
    }
}
