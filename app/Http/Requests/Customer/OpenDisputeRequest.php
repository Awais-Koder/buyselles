<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class OpenDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|integer|exists:orders,id',
            'order_detail_id' => 'nullable|integer|exists:order_details,id',
            'reason_id' => 'nullable|integer|exists:dispute_reasons,id',
            'description' => 'required|string|min:20|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => translate('order_id_is_required'),
            'order_id.exists' => translate('order_not_found'),
            'description.required' => translate('description_is_required'),
            'description.min' => translate('description_must_be_at_least_20_characters'),
            'description.max' => translate('description_must_not_exceed_2000_characters'),
        ];
    }
}
