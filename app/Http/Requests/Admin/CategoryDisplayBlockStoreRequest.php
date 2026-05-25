<?php

namespace App\Http\Requests\Admin;

use App\Enums\CategoryDisplayBlockType;
use App\Models\Category;
use App\Models\CategoryDisplayBlock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CategoryDisplayBlockStoreRequest extends FormRequest
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
            'block_type' => ['required', 'string', Rule::in(CategoryDisplayBlockType::values())],
            'title' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => translate('category_is_required'),
            'block_type.required' => translate('block_type_is_required'),
            'block_type.in' => translate('invalid_block_type'),
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

                $exists = CategoryDisplayBlock::query()
                    ->where('category_id', $this->integer('category_id'))
                    ->where('block_type', $this->string('block_type')->toString())
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'block_type',
                        translate('this_block_type_is_already_added_for_the_category')
                    );
                }

                if (! Category::query()->whereKey($this->integer('category_id'))->where('position', 0)->exists()) {
                    $validator->errors()->add('category_id', translate('display_blocks_are_only_for_main_categories'));
                }
            },
        ];
    }
}
