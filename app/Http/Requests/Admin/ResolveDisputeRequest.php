<?php

namespace App\Http\Requests\Admin;

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
            'decision' => 'required|string|min:10|max:2000',
            'resolution_type' => 'required|in:refund,release',
            'admin_note' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => translate('decision_is_required'),
            'decision.min' => translate('decision_must_be_at_least_10_characters'),
            'resolution_type.required' => translate('resolution_type_is_required'),
            'resolution_type.in' => translate('resolution_type_must_be_refund_or_release'),
        ];
    }
}
