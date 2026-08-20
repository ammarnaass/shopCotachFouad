<?php

namespace App\Modules\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShippingZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-shipping') ?? false;
    }

    public function rules(): array
    {
        return [
            'shipping_company_id' => ['nullable', 'exists:shipping_companies,id'],
            'name' => ['required', 'string', 'max:150'],

            // إجباري: أكواد ISO alpha-2 نصية فقط (مثال DZ) — يمنع تكرار باگ IDs الرقمية
            'countries' => ['required', 'array', 'min:1'],
            'countries.*' => ['required', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],

            'states' => ['nullable', 'array'],
            'states.*' => ['string', 'max:100'],

            'cities' => ['nullable', 'array'],
            'cities.*' => ['string', 'max:100'],

            'is_default' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'countries.required' => 'يجب تحديد دولة واحدة على الأقل بصيغة كود ISO (مثال: DZ).',
            'countries.*.regex' => 'كود الدولة يجب أن يكون حرفين لاتينيين فقط (مثال: DZ, MA, TN).',
        ];
    }
}
