<?php

namespace App\Enums;

enum CategoryDisplayBlockType: string
{
    case SubCategories = 'sub_categories';
    case SubCategoryProducts = 'sub_category_products';
    case SubSubCategories = 'sub_sub_categories';
    case SubSubCategoryProducts = 'sub_sub_category_products';
    case MixedProducts = 'mixed_products';
    case VendorsList = 'vendors_list';
    case LocationPipeline = 'location_pipeline';

    public function label(): string
    {
        return match ($this) {
            self::SubCategories => translate('Sub_Category'),
            self::SubCategoryProducts => translate('Products_in_Sub_Category'),
            self::SubSubCategories => translate('Sub_Sub_Category'),
            self::SubSubCategoryProducts => translate('Products_in_Sub_Sub_Category'),
            self::MixedProducts => translate('Mixed_Products_from_All_Vendors'),
            self::VendorsList => translate('Vendors_List'),
            self::LocationPipeline => translate('Full_Location_Filtering_Pipeline'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SubCategories => translate('displays_sub_categories_grid_under_main_category'),
            self::SubCategoryProducts => translate('displays_products_filtered_by_sub_categories'),
            self::SubSubCategories => translate('displays_sub_sub_categories_hierarchy'),
            self::SubSubCategoryProducts => translate('displays_products_in_sub_sub_categories'),
            self::MixedProducts => translate('mixed_products_with_location_filters_and_search'),
            self::VendorsList => translate('vendors_list_with_location_filters_and_search'),
            self::LocationPipeline => translate('location_filters_best_selling_products_verified_merchants'),
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
