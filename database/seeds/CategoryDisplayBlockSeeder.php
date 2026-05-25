<?php

namespace Database\Seeders;

use App\Enums\CategoryDisplayBlockType;
use App\Models\Category;
use App\Models\CategoryDisplayBlock;
use Illuminate\Database\Seeder;

class CategoryDisplayBlockSeeder extends Seeder
{
    /**
     * Default block order for every main category (position 0).
     *
     * @return array<int, array{block_type: string, settings: array<string, string>|null}>
     */
    public static function defaultBlocksForCategory(Category $category): array
    {
        $categoryName = $category->name;

        return [
            [
                'block_type' => CategoryDisplayBlockType::SubCategories->value,
                'settings' => null,
            ],
            [
                'block_type' => CategoryDisplayBlockType::SubCategoryProducts->value,
                'settings' => null,
            ],
            [
                'block_type' => CategoryDisplayBlockType::SubSubCategories->value,
                'settings' => null,
            ],
            [
                'block_type' => CategoryDisplayBlockType::SubSubCategoryProducts->value,
                'settings' => null,
            ],
            [
                'block_type' => CategoryDisplayBlockType::MixedProducts->value,
                'settings' => [
                    'title' => 'All '.$categoryName.' Products',
                ],
            ],
            [
                'block_type' => CategoryDisplayBlockType::VendorsList->value,
                'settings' => [
                    'title' => 'Shops in '.$categoryName,
                ],
            ],
            [
                'block_type' => CategoryDisplayBlockType::LocationPipeline->value,
                'settings' => [
                    'title' => 'Discover Local',
                ],
            ],
        ];
    }

    public function run(): void
    {
        $this->seedCategories();
    }

    /**
     * @param  array<int>|null  $categoryIds  Main category IDs only; null seeds all.
     * @return array{categories: int, blocks_created: int, blocks_updated: int}
     */
    public function seedCategories(?array $categoryIds = null, bool $resetExisting = false): array
    {
        $query = Category::query()->where('position', 0);

        if ($categoryIds !== null && $categoryIds !== []) {
            $query->whereIn('id', $categoryIds);
        }

        $categories = $query->orderBy('id')->get();

        $stats = [
            'categories' => 0,
            'blocks_created' => 0,
            'blocks_updated' => 0,
        ];

        foreach ($categories as $category) {
            if ($resetExisting) {
                CategoryDisplayBlock::query()->where('category_id', $category->id)->delete();
            }

            $stats['categories']++;

            foreach (self::defaultBlocksForCategory($category) as $position => $blockConfig) {
                $existing = CategoryDisplayBlock::query()
                    ->where('category_id', $category->id)
                    ->where('block_type', $blockConfig['block_type'])
                    ->first();

                CategoryDisplayBlock::query()->updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'block_type' => $blockConfig['block_type'],
                    ],
                    [
                        'position' => $position,
                        'is_active' => true,
                        'settings' => $blockConfig['settings'],
                    ]
                );

                if ($existing === null) {
                    $stats['blocks_created']++;
                } else {
                    $stats['blocks_updated']++;
                }
            }
        }

        return $stats;
    }
}
