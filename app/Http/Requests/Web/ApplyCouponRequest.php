<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|min:2|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'كود الكوبون مطلوب',
            'code.min' => 'كود الكوبون قصير جداً',
        ];
    }
}
