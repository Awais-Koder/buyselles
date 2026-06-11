<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryDisplayBlockWebService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryDisplayBlockController extends Controller
{
    public function __construct(
        private readonly CategoryDisplayBlockWebService $displayBlockService,
    ) {}

    public function mixedProducts(Request $request, int $categoryId): JsonResponse
    {
        $category = $this->resolveMainCategory($categoryId);

        return response()->json([
            'html' => view('category-display-blocks._products-grid', [
                'products' => $this->displayBlockService->getMixedProducts($category->id, $request),
                'themeKey' => theme_root_path(),
            ])->render(),
        ]);
    }

    public function vendors(Request $request, int $categoryId): JsonResponse
    {
        $category = $this->resolveMainCategory($categoryId);
        $context = array_filter([
            'parent_id' => $request->filled('parent_id') ? $request->integer('parent_id') : null,
            'parent_name' => $request->filled('parent_name') ? (string) $request->string('parent_name') : null,
            'vendor_id' => $request->filled('vendor_id') ? $request->integer('vendor_id') : null,
            'vendor_name' => $request->filled('vendor_name') ? (string) $request->string('vendor_name') : null,
        ], fn ($value) => $value !== null && $value !== '');

        return response()->json([
            'html' => view('category-display-blocks._vendors-grid', [
                'vendors' => $this->displayBlockService->getVendors($category->id, $request),
                'category' => $category,
                'themeKey' => theme_root_path(),
                'canSelectVendor' => $this->displayBlockService->hasFollowingBlockAfterVendor($category->id),
                'context' => $context,
            ])->render(),
        ]);
    }

    public function locationPipeline(Request $request, int $categoryId): JsonResponse
    {
        $category = $this->resolveMainCategory($categoryId);
        $data = $this->displayBlockService->getLocationPipelineData($category->id, $request);

        return response()->json([
            'html' => view('category-display-blocks._location-pipeline-results', [
                'products' => $data['products'],
                'vendors' => $data['vendors'],
                'locationLabel' => $data['location_label'],
                'themeKey' => theme_root_path(),
            ])->render(),
        ]);
    }

    private function resolveMainCategory(int $categoryId): Category
    {
        return Category::query()
            ->whereKey($categoryId)
            ->where('position', 0)
            ->firstOrFail();
    }
}
