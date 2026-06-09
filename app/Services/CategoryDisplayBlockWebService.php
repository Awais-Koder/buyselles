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
     * @return Collection<int, Category>
     */
    public function getSubCategoriesWithSubSub(Category $category): Collection
    {
        return Category::query()
            ->where('parent_id', $category->id)
            ->where('position', 1)
            ->with(['childes' => function ($query) {
                $query->where('position', 2)->orderBy('priority');
            }])
            ->orderBy('priority')
            ->get();
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
     * @return array<int, array{category: Category, products: LengthAwarePaginator|Collection}>
     */
    public function getSubCategoryGroupedProducts(Category $category, Request $request, int $limit = self::PREVIEW_LIMIT): array
    {
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
     * @return array<int, array{category: Category, products: LengthAwarePaginator|Collection}>
     */
    public function getSubSubCategoryGroupedProducts(Category $category, Request $request, int $limit = self::PREVIEW_LIMIT): array
    {
        $subCategories = $this->getSubCategoriesWithSubSub($category);
        $groupedProducts = [];

        foreach ($subCategories as $subCategory) {
            foreach ($subCategory->childes as $subSubCategory) {
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
     * @return array<string, mixed>
     */
    public function resolveBlockViewData(CategoryDisplayBlock $block, Category $category, Request $request): array
    {
        return match ($block->block_type) {
            CategoryDisplayBlockType::SubCategories->value => [
                'subCategories' => $this->getSubCategories($category),
            ],
            CategoryDisplayBlockType::SubCategoryProducts->value => [
                'groupedProducts' => $this->getSubCategoryGroupedProducts($category, $request),
            ],
            CategoryDisplayBlockType::SubSubCategoryProducts->value => [
                'groupedProducts' => $this->getSubSubCategoryGroupedProducts($category, $request),
            ],
            CategoryDisplayBlockType::SubSubCategories->value => [
                'subCategoriesWithChildren' => $this->getSubCategoriesWithSubSub($category),
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
}
