<?php

namespace App\Services;

use App\Enums\CategoryDisplayBlockType;
use App\Models\Category;
use App\Models\CategoryDisplayBlock;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Utils\CategoryManager;
use App\Utils\ProductManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class CategoryDisplayBlockWebService
{
    public const PREVIEW_LIMIT = 12;

    public function hasActiveBlocks(int $categoryId): bool
    {
        return CategoryDisplayBlock::query()
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * @return Collection<int, CategoryDisplayBlock>
     */
    public function getActiveBlocks(int $categoryId): Collection
    {
        return CategoryDisplayBlock::query()
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('position')
            ->get();
    }

    public function blockTitle(CategoryDisplayBlock $block, Category $category): string
    {
        $customTitle = $block->settings['title'] ?? null;
        if (is_string($customTitle) && $customTitle !== '') {
            return $customTitle;
        }

        $type = CategoryDisplayBlockType::tryFrom($block->block_type);

        return $type?->label() ?? ucwords(str_replace('_', ' ', $block->block_type));
    }

    /**
     * @return Collection<int, Category>
     */
    public function getSubCategories(Category $category): Collection
    {
        return $category->childes()->orderBy('priority')->get();
    }

    /**
     * @return LengthAwarePaginator<int, Product>|Collection<int, Product>
     */
    public function getProductsForCategory(int $categoryId, Request $request, int $limit = self::PREVIEW_LIMIT): LengthAwarePaginator|Collection
    {
        $request->merge([
            'limit' => $limit,
            'offset' => $request->integer('page', 1),
        ]);

        return CategoryManager::products($categoryId, $request, $limit);
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function getMixedProducts(int $categoryId, Request $request, int $perPage = self::PREVIEW_LIMIT): LengthAwarePaginator
    {
        $filterRequest = clone $request;
        $filterRequest->merge([
            'data_from' => 'category',
            'category_id' => $categoryId,
            'product_name' => $request->input('search', $request->input('product_name')),
        ]);

        $products = ProductManager::getProductListData(request: $filterRequest);

        if ($products instanceof Builder) {
            $this->applyShopLocationFilter($products, $request);

            return $products
                ->paginate($perPage)
                ->appends($request->only(['search', 'product_name', 'country_id', 'city_id', 'area_id', 'page']));
        }

        if (! $products instanceof Collection) {
            $products = collect($products);
        }

        $products = $this->filterProductsCollectionByShopLocation($products, $request);

        return $this->paginateProductCollection($products, $request, $perPage);
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return Collection<int, Product>
     */
    public function filterProductsCollectionByShopLocation(Collection $products, Request $request): Collection
    {
        if (! $request->hasAny(['country_id', 'city_id', 'area_id'])) {
            return $products;
        }

        return $products->filter(function (Product $product) use ($request) {
            if ($product->product_type === 'digital') {
                return true;
            }

            if ($product->category?->category_type === 'digital') {
                return true;
            }

            $shop = $product->shop ?? $product->seller?->shop;

            if ($shop === null) {
                return false;
            }

            if ($request->filled('country_id') && (int) $shop->store_country_id !== $request->integer('country_id')) {
                return false;
            }

            if ($request->filled('city_id') && (int) $shop->store_city_id !== $request->integer('city_id')) {
                return false;
            }

            if ($request->filled('area_id') && (int) $shop->store_area_id !== $request->integer('area_id')) {
                return false;
            }

            return true;
        })->values();
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateProductCollection(Collection $products, Request $request, int $perPage): LengthAwarePaginator
    {
        $page = max(1, $request->integer('page', 1));
        $items = $products->forPage($page, $perPage)->values();

        return (new LengthAwarePaginator(
            $items,
            $products->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()],
        ))->appends($request->only(['search', 'product_name', 'country_id', 'city_id', 'area_id', 'page']));
    }

    /**
     * Filter by vendor shop location (store country / city / area).
     *
     * @param  Builder<Product>  $query
     */
    public function applyShopLocationFilter(Builder $query, Request $request): void
    {
        if (! $request->hasAny(['country_id', 'city_id', 'area_id'])) {
            return;
        }

        $query->where(function ($subQuery) use ($request) {
            $subQuery->whereHas('shop', function ($shopQuery) use ($request) {
                if ($request->filled('country_id')) {
                    $shopQuery->where('store_country_id', $request->integer('country_id'));
                }
                if ($request->filled('city_id')) {
                    $shopQuery->where('store_city_id', $request->integer('city_id'));
                }
                if ($request->filled('area_id')) {
                    $shopQuery->where('store_area_id', $request->integer('area_id'));
                }
            })
                ->orWhere('product_type', 'digital')
                ->orWhereHas('category', function ($categoryQuery) {
                    $categoryQuery->where('category_type', 'digital');
                });
        });
    }

    /**
     * @return LengthAwarePaginator<int, Seller>
     */
    public function getVendors(int $categoryId, Request $request, int $perPage = self::PREVIEW_LIMIT): LengthAwarePaginator
    {
        $vendors = Seller::approved()
            ->with(['shop'])
            ->whereHas('shop', function ($query) use ($request) {
                if ($request->filled('country_id')) {
                    $query->where('store_country_id', $request->integer('country_id'));
                }
                if ($request->filled('city_id')) {
                    $query->where('store_city_id', $request->integer('city_id'));
                }
                if ($request->filled('area_id')) {
                    $query->where('store_area_id', $request->integer('area_id'));
                }
                if ($request->filled('search')) {
                    $query->where('name', 'like', '%'.$request->string('search').'%');
                }
            })
            ->whereHas('product', function ($productQuery) use ($categoryId) {
                $categoryIdFragment = '"'.$categoryId.'"';
                $productQuery->active()->where('category_ids', 'like', '%'.$categoryIdFragment.'%');
            })
            ->withCount(['product' => function ($query) {
                $query->active();
            }])
            ->paginate($perPage)
            ->appends($request->only(['search', 'country_id', 'city_id', 'area_id', 'page']));

        $vendors->getCollection()->transform(function ($seller) {
            $seller['average_rating'] = Review::active()->whereHas('product', function ($query) use ($seller) {
                $query->where('user_id', $seller->id)->where('added_by', 'seller');
            })->avg('rating') ?? 0;
            $seller['review_count'] = Review::active()->whereHas('product', function ($query) use ($seller) {
                $query->where('user_id', $seller->id)->where('added_by', 'seller');
            })->count();

            return $seller;
        });

        return $vendors;
    }

    /**
     * @return array{products: LengthAwarePaginator<int, Product>, vendors: LengthAwarePaginator<int, Seller>, location_label: string}
     */
    public function getLocationPipelineData(int $categoryId, Request $request, int $perPage = self::PREVIEW_LIMIT): array
    {
        $locationLabel = $this->resolveLocationLabel($request);

        $productRequest = clone $request;
        $productRequest->merge(['category_id' => $categoryId]);

        $categoryIdFragment = '"'.$categoryId.'"';

        $products = Product::active()
            ->where('category_ids', 'like', '%'.$categoryIdFragment.'%')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->string('search').'%');
            });

        $this->applyShopLocationFilter($products, $request);

        $products = $products
            ->withCount(['orderDetails', 'reviews', 'wishList'])
            ->with(['reviews', 'rating', 'shop'])
            ->orderBy('order_details_count', 'desc')
            ->paginate($perPage, ['*'], 'products_page', $request->integer('products_page', 1));

        $vendorRequest = clone $request;
        $vendorsQuery = Seller::approved()
            ->with(['shop'])
            ->whereHas('shop', function ($query) use ($vendorRequest) {
                if ($vendorRequest->filled('country_id')) {
                    $query->where('store_country_id', $vendorRequest->integer('country_id'));
                }
                if ($vendorRequest->filled('city_id')) {
                    $query->where('store_city_id', $vendorRequest->integer('city_id'));
                }
                if ($vendorRequest->filled('area_id')) {
                    $query->where('store_area_id', $vendorRequest->integer('area_id'));
                }
            })
            ->whereHas('product', function ($productQuery) use ($categoryId) {
                $categoryIdFragment = '"'.$categoryId.'"';
                $productQuery->active()->where('category_ids', 'like', '%'.$categoryIdFragment.'%');
            })
            ->withCount(['product' => function ($query) {
                $query->active();
            }]);

        $vendors = $vendorsQuery
            ->paginate($perPage, ['*'], 'vendors_page', $vendorRequest->integer('vendors_page', 1))
            ->appends($vendorRequest->only(['country_id', 'city_id', 'area_id', 'vendors_page']));

        $vendors->getCollection()->transform(function ($seller) {
            $seller['average_rating'] = Review::active()->whereHas('product', function ($query) use ($seller) {
                $query->where('user_id', $seller->id)->where('added_by', 'seller');
            })->avg('rating') ?? 0;
            $seller['review_count'] = Review::active()->whereHas('product', function ($query) use ($seller) {
                $query->where('user_id', $seller->id)->where('added_by', 'seller');
            })->count();

            return $seller;
        });

        return [
            'products' => $products,
            'vendors' => $vendors,
            'location_label' => $locationLabel,
        ];
    }

    public function resolveLocationLabel(Request $request): string
    {
        if ($request->filled('area_id')) {
            $area = \App\Models\LocationArea::find($request->integer('area_id'));

            return $area?->name ?? translate('selected_location');
        }

        if ($request->filled('city_id')) {
            $city = \App\Models\LocationCity::find($request->integer('city_id'));

            return $city?->name ?? translate('selected_location');
        }

        if ($request->filled('country_id')) {
            $country = \App\Models\LocationCountry::find($request->integer('country_id'));

            return $country?->name ?? translate('selected_location');
        }

        return translate('your_area');
    }

    /**
     * @return LengthAwarePaginator<int, Product>|Collection<int, Product>
     */
    public function getProductsForSubCategoryOnly(int $subCategoryId, Request $request, int $limit = self::PREVIEW_LIMIT): LengthAwarePaginator|Collection
    {
        $scopedRequest = clone $request;
        $scopedRequest->merge([
            'limit' => $limit,
            'offset' => $request->integer('page', 1),
            'filter_by' => 'direct_sub_category',
        ]);

        return CategoryManager::products($subCategoryId, $scopedRequest, $limit);
    }

    /**
     * @return LengthAwarePaginator<int, Product>|Collection<int, Product>
     */
    public function getProductsForSubSubCategoryOnly(int $subSubCategoryId, Request $request, int $limit = self::PREVIEW_LIMIT): LengthAwarePaginator|Collection
    {
        $scopedRequest = clone $request;
        $scopedRequest->merge([
            'limit' => $limit,
            'offset' => $request->integer('page', 1),
            'filter_by' => 'direct_sub_sub_category',
        ]);

        return CategoryManager::products($subSubCategoryId, $scopedRequest, $limit);
    }

    /**
     * @param  array{parent_id?: int, parent_name?: string}  $context
     * @return array<string, mixed>
     */
    public function resolveBlockViewData(CategoryDisplayBlock $block, Category $category, Request $request, array $context = []): array
    {
        return match ($block->block_type) {
            CategoryDisplayBlockType::SubCategories->value => [
                'subCategories' => $this->getSubCategories($category),
            ],
            CategoryDisplayBlockType::SubCategoryProducts->value => [
                'groupedProducts' => $this->getSubCategoryGroupedProducts($category, $request, $context),
            ],
            CategoryDisplayBlockType::SubSubCategoryProducts->value => [
                'groupedProducts' => $this->getSubSubCategoryGroupedProducts($category, $request, $context),
            ],
            CategoryDisplayBlockType::SubSubCategories->value => [
                'subCategoriesWithChildren' => $this->getSubCategoriesWithSubSub($category, $context),
            ],
            CategoryDisplayBlockType::MixedProducts->value => [
                'products' => $this->getMixedProducts($category->id, $request),
            ],
            CategoryDisplayBlockType::VendorsList->value => [
                'vendors' => $this->getVendors($category->id, $request),
            ],
            CategoryDisplayBlockType::LocationPipeline->value => $this->getLocationPipelineData($category->id, $request),
            default => [],
        };
    }

    /**
     * @return array<int, string>
     */
    public function navigationBlockTypes(): array
    {
        return [
            CategoryDisplayBlockType::SubCategories->value,
            CategoryDisplayBlockType::SubCategoryProducts->value,
            CategoryDisplayBlockType::SubSubCategories->value,
            CategoryDisplayBlockType::SubSubCategoryProducts->value,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function terminalBlockTypes(): array
    {
        return [
            CategoryDisplayBlockType::LocationPipeline->value,
            CategoryDisplayBlockType::MixedProducts->value,
            CategoryDisplayBlockType::VendorsList->value,
        ];
    }

    public function isNavigationBlock(string $blockType): bool
    {
        return in_array($blockType, $this->navigationBlockTypes(), true);
    }

    public function isTerminalBlock(string $blockType): bool
    {
        return in_array($blockType, $this->terminalBlockTypes(), true);
    }

    /**
     * @param  Collection<int, CategoryDisplayBlock>  $blocks
     * @param  array{parent_id?: int, parent_name?: string}  $context
     * @return array<int, int>
     */
    public function getDataBlockIndices(Collection $blocks, Category $category, array $context = []): array
    {
        $indices = [];

        foreach ($blocks as $index => $block) {
            if ($this->blockHasData($block, $category, $context)) {
                $indices[] = $index;
            }
        }

        return $indices;
    }

    /**
     * @param  Collection<int, CategoryDisplayBlock>  $blocks
     * @param  array{parent_id?: int, parent_name?: string}  $context
     */
    public function hasNavigationBlockWithData(Collection $blocks, Category $category, array $context = []): bool
    {
        foreach ($blocks as $block) {
            if ($this->isNavigationBlock($block->block_type)
                && $this->blockHasData($block, $category, $context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Collection<int, CategoryDisplayBlock>  $blocks
     * @param  array{parent_id?: int, parent_name?: string}  $context
     * @return array{shouldExitToCategories: bool, stepIndex: int|null, dataBlockIndices: array<int, int>}
     */
    public function resolveInitialStep(Collection $blocks, Category $category, array $context = []): array
    {
        $dataBlockIndices = $this->getDataBlockIndices($blocks, $category, $context);

        if ($dataBlockIndices === []) {
            return [
                'shouldExitToCategories' => true,
                'stepIndex' => null,
                'dataBlockIndices' => $dataBlockIndices,
            ];
        }

        $hasNavigationData = $this->hasNavigationBlockWithData($blocks, $category, $context);
        $firstIndex = $dataBlockIndices[0];
        $firstBlock = $blocks[$firstIndex] ?? null;

        if (! $hasNavigationData && $firstBlock && $this->isTerminalBlock($firstBlock->block_type)) {
            return [
                'shouldExitToCategories' => true,
                'stepIndex' => null,
                'dataBlockIndices' => $dataBlockIndices,
            ];
        }

        return [
            'shouldExitToCategories' => false,
            'stepIndex' => $firstIndex,
            'dataBlockIndices' => $dataBlockIndices,
        ];
    }

    /**
     * @param  Collection<int, CategoryDisplayBlock>  $blocks
     * @param  array{parent_id?: int, parent_name?: string}  $context
     */
    public function findPreviousDataStepIndex(Collection $blocks, Category $category, int $currentStep, array $context = []): ?int
    {
        for ($step = $currentStep - 1; $step >= 0; $step--) {
            $block = $blocks[$step] ?? null;
            if ($block && $this->blockHasData($block, $category, $context)) {
                return $step;
            }
        }

        return null;
    }

    /**
     * @param  Collection<int, CategoryDisplayBlock>  $blocks
     * @param  array{parent_id?: int, parent_name?: string}  $context
     */
    public function findNextDataStepIndex(Collection $blocks, Category $category, int $currentStep, array $context = []): ?int
    {
        for ($step = $currentStep + 1; $step < $blocks->count(); $step++) {
            $block = $blocks[$step] ?? null;
            if ($block && $this->blockHasData($block, $category, $context)) {
                return $step;
            }
        }

        return null;
    }

    /**
     * @param  Collection<int, CategoryDisplayBlock>  $blocks
     * @param  array{parent_id?: int, parent_name?: string}  $context
     * @return array{parent_id?: int, parent_name?: string}
     */
    public function contextForStep(Collection $blocks, int $stepIndex, array $context = []): array
    {
        $block = $blocks[$stepIndex] ?? null;

        if ($block === null) {
            return $context;
        }

        if (in_array($block->block_type, [
            CategoryDisplayBlockType::SubCategories->value,
            CategoryDisplayBlockType::SubCategoryProducts->value,
        ], true)) {
            unset($context['parent_id'], $context['parent_name']);
        }

        return $context;
    }

    /**
     * Get the active block for a specific step, auto-skipping blocks that have no data.
     *
     * @param  array{parent_id?: int, parent_name?: string}  $context
     * @return array{block: CategoryDisplayBlock|null, stepIndex: int, hasNext: bool, hasPrev: bool, totalSteps: int, displayStepNumber: int, displayTotalSteps: int, dataBlockIndices: array<int, int>, previousStepIndex: int|null, nextStepIndex: int|null, backContext: array{parent_id?: int, parent_name?: string}, title: string, data: array}
     */
    public function getActiveBlockForStep(Category $category, int $step, Request $request, array $context = []): array
    {
        $blocks = $this->getActiveBlocks($category->id);

        if ($blocks->isEmpty()) {
            return [
                'block' => null,
                'stepIndex' => 0,
                'hasNext' => false,
                'hasPrev' => false,
                'totalSteps' => 0,
                'displayStepNumber' => 0,
                'displayTotalSteps' => 0,
                'dataBlockIndices' => [],
                'previousStepIndex' => null,
                'nextStepIndex' => null,
                'backContext' => [],
                'title' => '',
                'data' => [],
            ];
        }

        $totalBlocks = $blocks->count();
        $step = max(0, min($step, $totalBlocks - 1));
        $direction = $request->string('direction', 'next');

        $block = $blocks[$step] ?? null;

        if ($block !== null && ! $this->blockHasData($block, $category, $context)) {
            if ($direction === 'back') {
                $previousStep = $this->findPreviousDataStepIndex($blocks, $category, $step, $context);
                if ($previousStep === null) {
                    $block = null;
                    $step = max(0, $step);
                } else {
                    $step = $previousStep;
                    $block = $blocks[$step] ?? null;
                    $context = $this->contextForStep($blocks, $step, $context);
                }
            } else {
                $nextStep = $this->findNextDataStepIndex($blocks, $category, $step, $context);
                if ($nextStep === null) {
                    $block = null;
                } else {
                    $step = $nextStep;
                    $block = $blocks[$step] ?? null;
                }
            }
        }

        $dataBlockIndices = $this->getDataBlockIndices($blocks, $category, $context);
        $displayStepNumber = $block === null ? 0 : (array_search($step, $dataBlockIndices, true) !== false
            ? array_search($step, $dataBlockIndices, true) + 1
            : $step + 1);
        $displayTotalSteps = count($dataBlockIndices);
        $previousStepIndex = $block === null ? null : $this->findPreviousDataStepIndex($blocks, $category, $step, $context);
        $nextStepIndex = $block === null ? null : $this->findNextDataStepIndex($blocks, $category, $step, $context);
        $backContext = $previousStepIndex !== null
            ? $this->contextForStep($blocks, $previousStepIndex, $context)
            : [];

        if ($block === null) {
            return [
                'block' => null,
                'stepIndex' => $step,
                'hasNext' => false,
                'hasPrev' => $previousStepIndex !== null,
                'totalSteps' => $totalBlocks,
                'displayStepNumber' => $displayStepNumber,
                'displayTotalSteps' => $displayTotalSteps,
                'dataBlockIndices' => $dataBlockIndices,
                'previousStepIndex' => $previousStepIndex,
                'nextStepIndex' => $nextStepIndex,
                'backContext' => $backContext,
                'title' => '',
                'data' => [],
            ];
        }

        return [
            'block' => $block,
            'stepIndex' => $step,
            'hasNext' => $nextStepIndex !== null,
            'hasPrev' => $previousStepIndex !== null,
            'totalSteps' => $totalBlocks,
            'displayStepNumber' => $displayStepNumber,
            'displayTotalSteps' => $displayTotalSteps,
            'dataBlockIndices' => $dataBlockIndices,
            'previousStepIndex' => $previousStepIndex,
            'nextStepIndex' => $nextStepIndex,
            'backContext' => $backContext,
            'title' => $this->blockTitle($block, $category),
            'data' => $this->resolveBlockViewData($block, $category, $request, $context),
        ];
    }

    /**
     * @param  array{parent_id?: int, parent_name?: string}  $context
     */
    public function blockHasData(CategoryDisplayBlock $block, Category $category, array $context = []): bool
    {
        if ($block->block_type === CategoryDisplayBlockType::LocationPipeline->value) {
            return true;
        }

        if (in_array($block->block_type, [
            CategoryDisplayBlockType::MixedProducts->value,
            CategoryDisplayBlockType::VendorsList->value,
        ], true)) {
            return true;
        }

        return match ($block->block_type) {
            CategoryDisplayBlockType::SubCategories->value => $this->getSubCategories($category)->isNotEmpty(),
            CategoryDisplayBlockType::SubCategoryProducts->value => $this->getSubCategories($category)->isNotEmpty(),
            CategoryDisplayBlockType::SubSubCategories->value => $this->subSubCategoriesExist($category, $context),
            CategoryDisplayBlockType::SubSubCategoryProducts->value => $this->subSubCategoriesExist($category, $context),
            default => true,
        };
    }

    /**
     * @param  array{parent_id?: int, parent_name?: string}  $context
     */
    private function subSubCategoriesExist(Category $category, array $context): bool
    {
        $parentId = isset($context['parent_id']) ? (int) $context['parent_id'] : null;

        if ($parentId) {
            $parentCategory = Category::query()->find($parentId);

            return $parentCategory && $parentCategory->childes()->where('position', 2)->exists();
        }

        foreach ($this->getSubCategories($category) as $subCategory) {
            if ($subCategory->childes()->where('position', 2)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{parent_id?: int, parent_name?: string}  $context
     * @return Collection<int, Category>
     */
    public function getSubCategoriesWithSubSub(Category $category, array $context = []): Collection
    {
        $parentId = isset($context['parent_id']) ? (int) $context['parent_id'] : null;

        $query = Category::query()
            ->where('parent_id', $category->id)
            ->where('position', 1)
            ->with(['childes' => function ($query) {
                $query->where('position', 2)->orderBy('priority');
            }])
            ->orderBy('priority');

        if ($parentId) {
            $query->whereKey($parentId);
        }

        return $query->get();
    }

    /**
     * @param  array{parent_id?: int, parent_name?: string}  $context
     * @return array<int, array{category: Category, products: LengthAwarePaginator|Collection}>
     */
    public function getSubCategoryGroupedProducts(Category $category, Request $request, array $context = [], int $limit = self::PREVIEW_LIMIT): array
    {
        $parentId = isset($context['parent_id']) ? (int) $context['parent_id'] : null;

        if ($parentId) {
            $subCategory = Category::query()->find($parentId);
            if (! $subCategory) {
                return [];
            }
            $products = $this->getProductsForSubCategoryOnly($subCategory->id, $request, $limit);
            if ($products->isNotEmpty()) {
                return [['category' => $subCategory, 'products' => $products]];
            }

            return [];
        }

        $subCategories = $this->getSubCategories($category);
        $groupedProducts = [];

        foreach ($subCategories as $subCategory) {
            $products = $this->getProductsForSubCategoryOnly($subCategory->id, $request, $limit);
            if ($products->isNotEmpty()) {
                $groupedProducts[] = [
                    'category' => $subCategory,
                    'products' => $products,
                ];
            }
        }

        return $groupedProducts;
    }

    /**
     * @param  array{parent_id?: int, parent_name?: string}  $context
     * @return array<int, array{category: Category, products: LengthAwarePaginator|Collection}>
     */
    public function getSubSubCategoryGroupedProducts(Category $category, Request $request, array $context = [], int $limit = self::PREVIEW_LIMIT): array
    {
        $parentId = isset($context['parent_id']) ? (int) $context['parent_id'] : null;

        if ($parentId) {
            $parentCategory = Category::query()->find($parentId);
            if (! $parentCategory) {
                return [];
            }
            $subCategories = [$parentCategory];
        } else {
            $subCategories = $this->getSubCategories($category);
        }

        $groupedProducts = [];

        foreach ($subCategories as $subCategory) {
            $subSubCategories = $subCategory->childes()->where('position', 2)->get();
            foreach ($subSubCategories as $subSubCategory) {
                $products = $this->getProductsForSubSubCategoryOnly($subSubCategory->id, $request, $limit);
                if ($products->isNotEmpty()) {
                    $groupedProducts[] = [
                        'category' => $subSubCategory,
                        'products' => $products,
                    ];
                }
            }
        }

        return $groupedProducts;
    }
}
