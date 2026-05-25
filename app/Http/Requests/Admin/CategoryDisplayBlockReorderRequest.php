<?php

namespace App\Http\Requests\Admin;

use App\Models\CategoryDisplayBlock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CategoryDisplayBlockReorderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('position', 0)],
            'block_ids' => ['required', 'array', 'min:1'],
            'block_ids.*' => ['required', 'integer', Rule::exists('category_display_blocks', 'id')],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $categoryId = $this->integer('category_id');
                $blockIds = $this->input('block_ids', []);

                $count = CategoryDisplayBlock::query()
                    ->where('category_id', $categoryId)
                    ->whereIn('id', $blockIds)
                    ->count();

                if ($count !== count($blockIds)) {
                    $validator->errors()->add('block_ids', translate('invalid_block_order'));
                }
            },
        ];
    }
}
