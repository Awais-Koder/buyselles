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

        return response()->json([
            'html' => view('category-display-blocks._vendors-grid', [
                'vendors' => $this->displayBlockService->getVendors($category->id, $request),
                'themeKey' => theme_root_path(),
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
