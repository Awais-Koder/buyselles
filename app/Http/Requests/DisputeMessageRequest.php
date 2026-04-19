<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DisputeMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('dispute_id') && $this->route('id')) {
            $this->merge(['dispute_id' => $this->route('id')]);
        }
    }

    public function rules(): array
    {
        return [
            'dispute_id' => 'required|integer|exists:disputes,id',
            'message' => 'required|string|min:1|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => translate('message_is_required'),
            'message.max' => translate('message_must_not_exceed_2000_characters'),
        ];
    }
}
