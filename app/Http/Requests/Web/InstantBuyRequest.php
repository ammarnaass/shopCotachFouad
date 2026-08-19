<?php

namespace App\Http\Requests\Web;

use App\Models\Settings\Setting;
use Illuminate\Foundation\Http\FormRequest;

class InstantBuyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'product_id' => 'required|exists:products,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'country_code' => 'required|string|size:2',
            'city' => 'required|string|max:100',
            'address' => 'required|string',
            'shipping_method' => 'required|in:standard,express',
            'delivery_type' => 'nullable|in:home,office',
            'shipping_company_id' => 'nullable|exists:shipping_companies,id',
            'quantity' => 'required|integer|min:1',
            'options' => 'nullable|array',
            'custom_text' => 'nullable|string|max:500',
        ];

        if (Setting::get('instant_show_email', '1') === '1' && Setting::get('instant_req_email', '0') === '1') {
            $rules['email'] = 'required|email|max:255';
        } else {
            $rules['email'] = 'nullable|email|max:255';
        }

        if (Setting::get('instant_show_state', '1') === '1' && Setting::get('instant_req_state', '0') === '1') {
            $rules['state_code'] = 'required|string|max:5';
        } else {
            $rules['state_code'] = 'nullable|string|max:5';
        }

        if (Setting::get('instant_show_district', '1') === '1' && Setting::get('instant_req_district', '0') === '1') {
            $rules['district'] = 'required|string|max:100';
        } else {
            $rules['district'] = 'nullable|string|max:100';
        }

        if (Setting::get('instant_show_zip', '1') === '1' && Setting::get('instant_req_zip', '0') === '1') {
            $rules['zip'] = 'required|string|max:20';
        } else {
            $rules['zip'] = 'nullable|string|max:20';
        }

        if (Setting::get('instant_show_notes', '1') === '1') {
            $rules['notes'] = 'nullable|string|max:500';
        }

        if (Setting::get('instant_show_coupon', '1') === '1') {
            $rules['coupon_code'] = 'nullable|string';
        }

        $allowedPaymentMethods = ['cod'];
        if (Setting::get('instant_enable_bank_transfer', '0') === '1') {
            $allowedPaymentMethods[] = 'bank';
            $allowedPaymentMethods[] = 'bank_transfer';
        }
        $rules['payment_method'] = 'required|in:'.implode(',', $allowedPaymentMethods);

        return $rules;
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'الاسم الأول مطلوب',
            'last_name.required' => 'اللقب مطلوب',
            'phone.required' => 'رقم الهاتف مطلوب',
            'country_code.required' => 'الدولة مطلوبة',
            'city.required' => 'المدينة مطلوبة',
            'address.required' => 'العنوان التفصيلي مطلوب',
        ];
    }
}
