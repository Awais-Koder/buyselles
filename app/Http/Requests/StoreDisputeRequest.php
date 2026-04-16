<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDisputeRequest extends FormRequest
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
            'description' => 'required|string|min:10|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => translate('order_id_is_required'),
            'order_id.exists' => translate('invalid_order'),
            'description.required' => translate('please_describe_your_issue'),
            'description.min' => translate('description_must_be_at_least_10_characters'),
            'description.max' => translate('description_must_not_exceed_2000_characters'),
        ];
    }
}
