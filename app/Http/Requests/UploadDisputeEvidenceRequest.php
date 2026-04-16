<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadDisputeEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dispute_id' => 'required|integer|exists:disputes,id',
            'files' => 'required|array|max:5',
            'files.*' => 'required|file|max:51200|mimes:jpg,jpeg,png,mp4',
            'caption' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'files.required' => translate('please_upload_at_least_one_file'),
            'files.max' => translate('maximum_5_files_allowed'),
            'files.*.max' => translate('file_size_must_not_exceed_50mb'),
            'files.*.mimes' => translate('allowed_file_types_jpg_png_mp4'),
        ];
    }
}
