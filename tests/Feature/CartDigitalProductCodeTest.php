<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Utils\CartManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CartDigitalProductCodeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('digital_product_codes');
        Schema::create('business_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->nullable();
            $table->longText('value')->nullable();
            $table->timestamps();
        });
        $this->app['db']->table('business_settings')->insert([
            'type' => 'language',
            'value' => json_encode([
                ['code' => 'en', 'name' => 'English', 'default' => true, 'direction' => 'ltr'],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('digital_product_codes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->text('code');
            $table->string('status')->default('available')->index();
            $table->boolean('is_active')->default(true);
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('digital_product_codes');
        Schema::dropIfExists('business_settings');
        Cache::flush();

        parent::tearDown();
    }

    public function test_ready_digital_product_without_available_codes_is_not_added_to_cart(): void
    {
        $request = Request::create('/api/v1/cart/add', 'POST', [
            'quantity' => 1,
        ]);

        $product = new Product([
            'digital_product_type' => 'ready_product',
        ]);
        $product->id = 123;

        $response = CartManager::addToCartDigitalProduct(
            request: $request,
            product: $product,
            shippingType: 'order_wise',
            sellerShippingList: null,
        );

        $this->assertSame(0, $response['status']);
        $this->assertSame(translate('out_of_stock!'), $response['message']);
    }
}
