<?php

namespace App\Http\Controllers\Admin\Product;

use App\Enums\CategoryDisplayBlockType;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\CategoryDisplayBlockReorderRequest;
use App\Http\Requests\Admin\CategoryDisplayBlockStoreRequest;
use App\Models\Category;
use App\Models\CategoryDisplayBlock;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryDisplayBlockController extends BaseController
{
    public function index(?Request $request, ?string $type = null): View|RedirectResponse
    {
        $request = $request ?? request();
        $categoryId = $request->integer('category_id');

        if ($categoryId <= 0) {
            ToastMagic::error(translate('category_is_required'));

            return redirect()->route('admin.category.view');
        }

        $category = Category::query()
            ->whereKey($categoryId)
            ->where('position', 0)
            ->first();

        if ($category === null) {
            ToastMagic::error(translate('display_blocks_are_only_for_main_categories'));

            return redirect()->route('admin.category.view');
        }

        $blocks = CategoryDisplayBlock::query()
            ->where('category_id', $category->id)
            ->orderBy('position')
            ->get();

        $usedBlockTypes = $blocks->pluck('block_type')->all();

        $availableBlockTypes = array_filter(
            CategoryDisplayBlockType::cases(),
            fn (CategoryDisplayBlockType $type): bool => ! in_array($type->value, $usedBlockTypes, true)
        );

        return view('admin-views.category.display-blocks', [
            'category' => $category,
            'blocks' => $blocks,
            'blockTypeLabels' => $this->blockTypeLabels(),
            'availableBlockTypes' => $availableBlockTypes,
        ]);
    }

    public function store(CategoryDisplayBlockStoreRequest $request): RedirectResponse
    {
        $categoryId = $request->integer('category_id');
        $maxPosition = CategoryDisplayBlock::query()
            ->where('category_id', $categoryId)
            ->max('position');

        $settings = null;
        if ($request->filled('title')) {
            $settings = ['title' => $request->string('title')->toString()];
        }

        CategoryDisplayBlock::query()->create([
            'category_id' => $categoryId,
            'block_type' => $request->string('block_type')->toString(),
            'position' => is_numeric($maxPosition) ? ((int) $maxPosition) + 1 : 0,
            'is_active' => true,
            'settings' => $settings,
        ]);

        ToastMagic::success(translate('block_added_successfully'));

        return redirect()->route('admin.category.display-blocks', ['category_id' => $categoryId]);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'id' => ['required', 'integer', Rule::exists('category_display_blocks', 'id')],
            'is_active' => ['required', 'boolean'],
        ]);

        $block = CategoryDisplayBlock::query()->findOrFail($request->integer('id'));
        $block->update(['is_active' => $request->boolean('is_active')]);

        return response()->json(['message' => translate('status_updated_successfully')]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:category_display_blocks,id'],
            'category_id' => ['required', 'integer'],
            'title' => ['nullable', 'string', 'max:120'],
        ]);

        $block = CategoryDisplayBlock::query()->findOrFail($validated['id']);
        $settings = $block->settings ?? [];
        $settings['title'] = $validated['title'] ?? null;
        $block->update(['settings' => array_filter($settings, fn ($value) => $value !== null && $value !== '')]);

        ToastMagic::success(translate('block_updated_successfully'));

        return redirect()->route('admin.category.display-blocks', ['category_id' => $validated['category_id']]);
    }

    public function reorder(CategoryDisplayBlockReorderRequest $request): JsonResponse
    {
        $categoryId = $request->integer('category_id');
        $blockIds = $request->input('block_ids', []);

        DB::transaction(function () use ($categoryId, $blockIds): void {
            foreach ($blockIds as $position => $blockId) {
                CategoryDisplayBlock::query()
                    ->where('category_id', $categoryId)
                    ->whereKey($blockId)
                    ->update(['position' => $position]);
            }
        });

        return response()->json(['message' => translate('block_order_updated_successfully')]);
    }

    public function delete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:category_display_blocks,id'],
            'category_id' => ['required', 'integer'],
        ]);

        CategoryDisplayBlock::query()->whereKey($validated['id'])->delete();

        $this->normalizePositions($validated['category_id']);

        ToastMagic::success(translate('block_deleted_successfully'));

        return redirect()->route('admin.category.display-blocks', ['category_id' => $validated['category_id']]);
    }

    /**
     * @return array<string, string>
     */
    private function blockTypeLabels(): array
    {
        $labels = [];
        foreach (CategoryDisplayBlockType::cases() as $type) {
            $labels[$type->value] = $type->label();
        }

        return $labels;
    }

    private function normalizePositions(int $categoryId): void
    {
        $blocks = CategoryDisplayBlock::query()
            ->where('category_id', $categoryId)
            ->orderBy('position')
            ->get();

        foreach ($blocks as $position => $block) {
            $block->update(['position' => $position]);
        }
    }
}
