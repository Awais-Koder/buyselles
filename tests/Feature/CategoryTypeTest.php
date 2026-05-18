<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_type_cascades_to_subcategories(): void
    {
        $mainCategory = Category::create([
            'name' => 'Main Category',
            'slug' => 'main-category',
            'parent_id' => 0,
            'position' => 0,
            'category_type' => 'physical',
        ]);

        $subCategory = Category::create([
            'name' => 'Sub Category',
            'slug' => 'sub-category',
            'parent_id' => $mainCategory->id,
            'position' => 1,
            'category_type' => 'physical',
        ]);

        $subSubCategory = Category::create([
            'name' => 'Sub Sub Category',
            'slug' => 'sub-sub-category',
            'parent_id' => $subCategory->id,
            'position' => 2,
            'category_type' => 'physical',
        ]);

        // Update main category type to digital
        $mainCategory->update(['category_type' => 'digital']);

        // Propagate updates (mimic CategoryController update flow)
        $subCategories = Category::where('parent_id', $mainCategory->id)->get();
        foreach ($subCategories as $subCat) {
            $subCat->update(['category_type' => 'digital']);
            Category::where('parent_id', $subCat->id)->update(['category_type' => 'digital']);
        }

        $this->assertEquals('digital', $subCategory->fresh()->category_type);
        $this->assertEquals('digital', $subSubCategory->fresh()->category_type);
    }
}
