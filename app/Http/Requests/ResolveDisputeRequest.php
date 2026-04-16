<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResolveDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dispute_id' => 'required|integer|exists:disputes,id',
            'resolution' => 'required|in:refund,release',
            'decision' => 'required|string|min:5|max:2000',
            'admin_note' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'resolution.required' => translate('please_select_a_resolution'),
            'resolution.in' => translate('invalid_resolution_type'),
            'decision.required' => translate('please_provide_a_decision_explanation'),
            'decision.min' => translate('decision_must_be_at_least_5_characters'),
        ];
    }
}
