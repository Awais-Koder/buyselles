# Plan: Dynamic Category Hierarchy with Block-Based Ordering

## Overview

Implement a configurable, block-based category display system where each main category can be configured to show different "blocks" (sections) in any order. The Flutter app reads the block configuration from the backend and dynamically renders each block.

---

## Architecture Decision

**Approach:** Backend stores block configuration per category. Flutter loads config first, then each block widget independently fetches its own data.

**Why this approach:** Different blocks need different data (categories vs. products vs. vendors). Having each block fetch its own data keeps widgets self-contained, avoid a massive single API response, and allows lazy loading as the user scrolls.

---

## Part 1 — Backend (Laravel)

### 1.1 New Migration: `category_display_blocks`

File: `database/migrations/YYYY_MM_DD_create_category_display_blocks_table.php`

```sql
category_display_blocks:
  id              bigint UNSIGNED PK
  category_id     bigint UNSIGNED FK -> categories(id) ON DELETE CASCADE
  block_type      varchar(50)    -- enum of block types
  position        int            -- sort order (0-based)
  is_active       tinyint(1)     -- default 1
  settings        json           -- nullable, block-specific config
  created_at, updated_at  timestamps

  INDEX: (category_id, position)
  INDEX: (category_id, is_active)
```

### 1.2 Block Types (block_type values)

| block_type | Display |
|---|---|
| `sub_categories` | Sub-categories list/grid |
| `sub_category_products` | Products filtered by sub-categories |
| `sub_sub_categories` | Sub-sub-categories hierarchy |
| `sub_sub_category_products` | Products filtered by sub-sub-categories |
| `mixed_products` | Mixed products from all vendors with active stock in this category + location filters + search bar |
| `vendors_list` | Vendors list with active products in this category + location filters + vendor name search |
| `location_pipeline` | Country→City→Area → "Best Selling Products in [Location]" → "Verified Merchants Operating in [Location]" |

### 1.3 New Model: `app/Models/CategoryDisplayBlock.php`

```php
class CategoryDisplayBlock extends Model
{
    protected $fillable = ['category_id', 'block_type', 'position', 'is_active', 'settings'];
    protected $casts = ['settings' => 'array', 'is_active' => 'boolean'];
    
    public function category(): BelongsTo { ... }
}
```

### 1.4 Update `app/Models/Category.php`

Add relationship:
```php
public function displayBlocks(): HasMany
{
    return $this->hasMany(CategoryDisplayBlock::class)
        ->where('is_active', true)
        ->orderBy('position');
}
```

### 1.5 New API Endpoint: `GET /api/v1/categories/{id}/display-blocks`

**File:** Add method to `app/Http/Controllers/Api/V1/CategoryController.php`

**Response shape:**
```json
{
  "category": { "id": 5, "name": "Electronics", "slug": "electronics", ... },
  "blocks": [
    {
      "id": 1,
      "block_type": "sub_categories",
      "position": 0,
      "settings": null
    },
    {
      "id": 2,
      "block_type": "mixed_products",
      "position": 1,
      "settings": { "title": "All Electronics Products" }
    },
    {
      "id": 3,
      "block_type": "vendors_list",
      "position": 2,
      "settings": null
    },
    {
      "id": 4,
      "block_type": "location_pipeline",
      "position": 3,
      "settings": { "title": "Discover Local" }
    }
  ]
}
```

**Route** (in `routes/rest_api/v1/api.php`):
```php
Route::get('categories/{id}/display-blocks', [CategoryController::class, 'getDisplayBlocks'])
    ->middleware('apiGuestCheck');
```

### 1.6 Supporting API Endpoints (mostly exist)

These endpoints already exist and will be called by individual block widgets:

| Endpoint | Block Type | Exists? |
|---|---|---|
| `GET /api/v1/categories` (with `parent_id` param) | sub_categories, sub_sub_categories | ✅ — `CategoryController@get_categories` |
| `GET /api/v1/categories/products/{id}` | sub_category_products, sub_sub_category_products | ✅ — `CategoryController@get_products` |
| `POST /api/v1/products/filter` | mixed_products | ✅ — `ProductController@getProductsFilter` |
| `GET /api/v1/discovery-vendors` | vendors_list, location_pipeline | ✅ — `GeneralController@discoveryVendors` |
| `GET /api/v1/discovery-products` | location_pipeline (best selling) | ✅ — `GeneralController@discoveryProducts` |
| `GET /api/v1/get-countries` | all location-filtered blocks | ✅ |
| `GET /api/v1/get-cities/{id}` | all location-filtered blocks | ✅ |
| `GET /api/v1/get-areas/{id}` | all location-filtered blocks | ✅ |

**Potential gap:** The `discovery-vendors` and `discovery-products` endpoints may need `category_id` filter support. Check `app/Http/Controllers/Api/V1/GeneralController.php` methods — if they don't accept a `category_id` param, add it as an optional filter.

---

## Part 2 — Flutter App (`mobile/User App`)

### 2.1 New Model: `CategoryDisplayBlockModel`

**File:** `lib/features/category/domain/models/category_display_block_model.dart`

```dart
class CategoryDisplayBlockResponse {
  final CategoryModel category;
  final List<DisplayBlock> blocks;
}

class DisplayBlock {
  final int id;
  final String blockType;   // 'sub_categories', 'mixed_products', etc.
  final int position;
  final Map<String, dynamic>? settings;
}
```

### 2.2 New API Endpoint Constants

**File:** `lib/utill/app_constants.dart`

```dart
static const String categoryDisplayBlocksUri = '/api/v1/categories/';
// Appended with: {id}/display-blocks
```

### 2.3 New Repository: `CategoryDisplayBlockRepository`

**File:** `lib/features/category/domain/repositories/category_display_block_repository.dart`

- Fetches `GET /api/v1/categories/{id}/display-blocks`
- Returns `CategoryDisplayBlockResponse`

### 2.4 New Controller: `CategoryDisplayBlockController`

**File:** `lib/features/category/controllers/category_display_block_controller.dart`

```dart
class CategoryDisplayBlockController extends ChangeNotifier {
  CategoryDisplayBlockResponse? response;
  bool isLoading = false;
  
  Future<void> loadBlocks(String categoryId);
  List<DisplayBlock> get activeBlocks;
}
```

Register in `di_container.dart` and add `ChangeNotifierProvider` in `main.dart`.

### 2.5 New Screen: `DynamicCategoryScreen`

**File:** `lib/features/category/screens/dynamic_category_screen.dart`

This screen:
1. Takes a `CategoryModel` parameter at construction
2. In `initState`, calls `CategoryDisplayBlockController.loadBlocks(categoryId)`
3. Body is a `ListView.builder` (or `CustomScrollView` with slivers) that iterates over `blocks` and renders the appropriate widget for each `block_type`
4. Each block type maps to a dedicated widget (see section 2.6)

**Widget mapping (by block_type):**
```dart
Widget _buildBlock(DisplayBlock block) {
  switch (block.blockType) {
    case 'sub_categories':
      return SubCategoriesBlock(categoryId: category.id);
    case 'sub_category_products':
      return SubCategoryProductsBlock(categoryId: category.id);
    case 'sub_sub_categories':
      return SubSubCategoriesBlock(categoryId: category.id);
    case 'sub_sub_category_products':
      return SubSubCategoryProductsBlock(categoryId: category.id);
    case 'mixed_products':
      return MixedProductsBlock(categoryId: category.id, settings: block.settings);
    case 'vendors_list':
      return VendorsListBlock(categoryId: category.id, settings: block.settings);
    case 'location_pipeline':
      return LocationPipelineBlock(categoryId: category.id, settings: block.settings);
    default:
      return const SizedBox.shrink();
  }
}
```

### 2.6 Block Widgets (all in `lib/features/category/widgets/`)

#### A. `SubCategoriesBlock` — `sub_categories_block_widget.dart`

Reuses the existing category grid pattern from `CategoryScreen`.

- Fetches `GET /api/v1/categories?parent_id={categoryId}` (sub-categories of the main category)
- Renders a grid of sub-category cards (reuse `CategoryDrillDownWidget`)
- Tapping a sub-category navigates to `DynamicCategoryScreen` for that sub-category (recursive) OR to `BrandAndCategoryProductScreen`

#### B. `SubCategoryProductsBlock` — `sub_category_products_block_widget.dart`

- Fetches `GET /api/v1/categories/products/{categoryId}` — this returns products for all sub-categories of the given category
- Renders a paginated masonry grid of `ProductWidget` (reuse `PaginatedListView` + `MasonryGridView`)
- Includes a title header like "Products in [Category Name]"

#### C. `SubSubCategoriesBlock` — `sub_sub_categories_block_widget.dart`

- Fetches `GET /api/v1/categories?parent_id={subCategoryId}` — but this needs the intermediate sub-categories first
- **Flow:** Fetch sub-categories → for each sub-category, show its sub-sub-categories in an expandable/grouped list
- Similar to the drill-down pattern in `CategoryScreen`'s right panel

#### D. `SubSubCategoryProductsBlock` — `sub_sub_category_products_block_widget.dart`

- Fetches products for sub-sub-categories of the main category
- Reuses `PaginatedListView` + `MasonryGridView` + `ProductWidget`

#### E. `MixedProductsBlock` — `mixed_products_block_widget.dart`

This is the "MIXED PRODUCTS FROM ALL VENDORS" block — products from vendors with active stock in this category.

**Sub-components:**
- Location filter bar (Country → City → Area) — reuse `SearchableLocationDialog` pattern from `PhysicalDiscoveryScreen` / `ProductFilterDialog`
- Search bar — reuse `SearchWidget` from `common/basewidget/search_widget.dart`
- Paginated masonry product grid — reuse `PaginatedListView` + `ProductWidget`

**API call:** `POST /api/v1/products/filter` with params:
- `category` → `[categoryId]`
- `location_country_id`, `location_city_id`, `location_area_id` (nullable)
- `search` → base64 encoded search term
- `limit`, `offset` for pagination

#### F. `VendorsListBlock` — `vendors_list_block_widget.dart`

**Sub-components:**
- Location filter bar (Country → City → Area) — same pattern as MixedProductsBlock
- Vendor name search bar
- List of vendor cards showing: shop logo, name, rating, product count, address

**API call:** `GET /api/v1/discovery-vendors?category_id={categoryId}&country_id={}&city_id={}&area_id={}&search={}&limit=&offset=`

**Vendor card tap:** Navigate to `TopSellerProductScreen` (vendor shop page).

#### G. `LocationPipelineBlock` — `location_pipeline_block_widget.dart`

The "FULL LOCATION FILTERING PIPELINE" block — this is a multi-step flow within a single block:

**Step 1:** Location selector (Country → City → Area) — cascading dropdowns

**Step 2:** After area is selected, display two sections:
- **"Best Selling Products in [Selected Location]"** — fetches `GET /api/v1/discovery-products?category_id={categoryId}&country_id={}&city_id={}&area_id={}` or `GET /api/v1/products/best-sellings` with location params
- **"Verified Merchants Operating in [Selected Location]"** — fetches `GET /api/v1/discovery-vendors?category_id={categoryId}&country_id={}&city_id={}&area_id={}` with verified filter

Reuses the location selector pattern from `PhysicalDiscoveryScreen` (`LocationFilterHeaderWidget` + `SearchableLocationDialog`).

### 2.7 Route Registration

**File:** `lib/utill/route_healper.dart`

Add or modify route:
```dart
GoRoute(
  path: '/category-hierarchy',
  pageBuilder: (context, state) {
    final categoryModel = state.extra as CategoryModel;
    return CustomTransitionPage(
      child: DynamicCategoryScreen(categoryModel: categoryModel),
      ...
    );
  },
),
```

**Modify `CategoryScreen`:** When a user taps a main category in the existing `CategoryScreen`, route to `DynamicCategoryScreen` instead of drilling into sub-categories inline. Keep the two-panel `CategoryScreen` for browsing all categories, but make the deep-dive use the new dynamic screen.

### 2.8 DI & Provider Registration

**File:** `lib/di_container.dart`
```dart
sl.registerLazySingleton(() => CategoryDisplayBlockRepository(dioClient: sl()));
sl.registerFactory(() => CategoryDisplayBlockController(repository: sl()));
```

**File:** `lib/main.dart`
```dart
ChangeNotifierProvider(create: (_) => sl<CategoryDisplayBlockController>()),
```

---

## Part 3 — Verification & Testing

### Backend Verification

1. Run migration: `php artisan migrate` — confirm `category_display_blocks` table is created
2. Seed test data: Insert blocks for a few categories via Tinker or seeder
3. Hit `GET /api/v1/categories/1/display-blocks` — confirm JSON response shape
4. Test each supporting endpoint with category_id filter to ensure they work correctly

### Flutter Verification

1. Build and run the app
2. Navigate to CategoryScreen → tap a main category → DynamicCategoryScreen opens
3. Verify blocks render in the configured order
4. Test each block type individually:
   - Sub-categories: grid displays, tap navigates deeper
   - Sub-category products: paginated product grid loads
   - Sub-sub-categories: hierarchy displays correctly
   - Mixed products: location filters work, search works, products load
   - Vendors list: location filters work, search works, vendor cards display
   - Location pipeline: cascading country→city→area, both sections display after selection
5. Test with a category that has NO blocks configured (should show a fallback or empty state)
6. Test with only 1-2 blocks configured

### Edge Cases

- Category with no sub-categories → `sub_categories` block shows empty state
- Category with no products → product blocks show "No products found"
- Location with no vendors → vendors block shows empty state
- Network errors → show retry widget (reuse existing error patterns from `PaginatedListView`)
- Deep hierarchy (category → sub-category → sub-sub-category → products) all as separate DynamicCategoryScreen instances — ensure navigation stack and back button work correctly

---

## File Change Summary

### Backend Files

| Action | File |
|---|---|
| **CREATE** | `database/migrations/YYYY_MM_DD_create_category_display_blocks_table.php` |
| **CREATE** | `app/Models/CategoryDisplayBlock.php` |
| **MODIFY** | `app/Models/Category.php` — add `displayBlocks()` relationship |
| **MODIFY** | `app/Http/Controllers/Api/V1/CategoryController.php` — add `getDisplayBlocks()` |
| **MODIFY** | `routes/rest_api/v1/api.php` — add route |
| **CHECK** | `app/Http/Controllers/Api/V1/GeneralController.php` — verify `discoveryVendors`/`discoveryProducts` support `category_id` param; add if missing |

### Flutter Files

| Action | File |
|---|---|
| **CREATE** | `lib/features/category/domain/models/category_display_block_model.dart` |
| **CREATE** | `lib/features/category/domain/repositories/category_display_block_repository.dart` |
| **CREATE** | `lib/features/category/controllers/category_display_block_controller.dart` |
| **CREATE** | `lib/features/category/screens/dynamic_category_screen.dart` |
| **CREATE** | `lib/features/category/widgets/sub_categories_block_widget.dart` |
| **CREATE** | `lib/features/category/widgets/sub_category_products_block_widget.dart` |
| **CREATE** | `lib/features/category/widgets/sub_sub_categories_block_widget.dart` |
| **CREATE** | `lib/features/category/widgets/sub_sub_category_products_block_widget.dart` |
| **CREATE** | `lib/features/category/widgets/mixed_products_block_widget.dart` |
| **CREATE** | `lib/features/category/widgets/vendors_list_block_widget.dart` |
| **CREATE** | `lib/features/category/widgets/location_pipeline_block_widget.dart` |
| **MODIFY** | `lib/utill/app_constants.dart` — add endpoint constant |
| **MODIFY** | `lib/di_container.dart` — register new repository + controller |
| **MODIFY** | `lib/main.dart` — add ChangeNotifierProvider |
| **MODIFY** | `lib/utill/route_healper.dart` — add route |
| **MODIFY** | `lib/features/category/screens/category_screen.dart` — wire tap to route to DynamicCategoryScreen |
