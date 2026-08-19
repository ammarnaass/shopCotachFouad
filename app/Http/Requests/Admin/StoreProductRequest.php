<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_fr' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_fr' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'short_description_en' => 'nullable|string|max:500',
            'short_description_fr' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|unique:products,sku',
            'stock' => 'required|integer|min:0',
            'type' => 'required|in:simple,variable,digital,bundle',
            'status' => 'required|in:active,inactive,draft',
            'featured' => 'boolean',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp,gif|max:2048',
            'options' => 'nullable|array',
            'options.*.name' => 'required_with:options|string|max:255',
            'options.*.type' => 'required_with:options|in:select,radio,color,text,file',
            'options.*.required' => 'boolean',
            'options.*.values' => 'nullable|array',
            'options.*.values.*.value' => 'required_with:options.*.values|string|max:255',
            'options.*.values.*.color_code' => 'nullable|string|max:20',
            'options.*.values.*.price_adjustment' => 'nullable|numeric',
            'options.*.values.*.stock' => 'nullable|integer',
            'shipping_company_id' => 'nullable|exists:shipping_companies,id',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.label' => 'required_with:custom_fields|string|max:255',
            'custom_fields.*.type' => 'required_with:custom_fields|in:text,textarea,file,number,calculated',
            'custom_fields.*.required' => 'boolean',
            'custom_fields.*.price_effect' => 'nullable|numeric',
            'weight' => 'nullable|numeric|min:0',
            'product_shipping_rules' => 'nullable|array',
            'product_shipping_rules.max_weight' => 'nullable|numeric|min:0',
            'product_shipping_rules.priority' => 'nullable|integer|min:0|max:999',
            'product_shipping_rules.fragile' => 'boolean',
            'product_shipping_rules.hazardous' => 'boolean',
            'product_shipping_rules.requires_signature' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'images.*.image' => 'يجب أن يكون الملف صورة بصيغة jpeg, jpg, png, webp أو gif',
            'images.*.max' => 'حجم الصورة يجب أن لا يتجاوز 2 ميجابايت',
        ];
    }
}
