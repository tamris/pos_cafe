<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductAndHppApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $cashierUser;
    protected Category $coffeeCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->cashierUser = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $this->coffeeCategory = Category::firstOrCreate(
            ['name' => 'Coffee'],
            ['description' => 'Menu kopi pilihan', 'is_active' => true]
        );
    }

    public function test_unauthenticated_cannot_access_product_and_hpp_api()
    {
        $resProduct = $this->getJson('/api/admin/products');
        $resProduct->assertStatus(401);

        $resHpp = $this->postJson('/api/admin/hpp/calculate', []);
        $resHpp->assertStatus(401);
    }

    public function test_cashier_role_forbidden_from_admin_product_and_hpp_api()
    {
        Sanctum::actingAs($this->cashierUser);

        $resProduct = $this->getJson('/api/admin/products');
        $resProduct->assertStatus(403);

        $resHpp = $this->postJson('/api/admin/hpp/calculate', []);
        $resHpp->assertStatus(403);
    }

    public function test_admin_can_calculate_hpp_simulation_accurately()
    {
        Sanctum::actingAs($this->adminUser);

        $payload = [
            'ingredients' => [
                [
                    'name' => 'Biji Kopi Espresso Blend',
                    'amount' => 18, // 18 gram
                    'unit' => 'gram',
                    'buy_price' => 200000, // Rp 200.000 / kg
                    'buy_amount' => 1,
                    'buy_unit' => 'kg',
                ],
                [
                    'name' => 'Fresh Milk Diamond',
                    'amount' => 150, // 150 ml
                    'unit' => 'ml',
                    'buy_price' => 20000, // Rp 20.000 / liter
                    'buy_amount' => 1,
                    'buy_unit' => 'liter',
                ],
                [
                    'name' => 'Cup Plastik 14oz',
                    'amount' => 1,
                    'unit' => 'pcs',
                    'buy_price' => 25000, // Rp 25.000 / 50 pcs (Rp 500/pcs)
                    'buy_amount' => 50,
                    'buy_unit' => 'pcs',
                ],
            ],
            'operational_cost' => 1000,
            'price' => 22000,
            'target_laba_bulanan' => 6000000,
        ];

        $response = $this->postJson('/api/admin/hpp/calculate', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');

        // Biji kopi: 18 * 0.001 * 200.000 = 3.600
        $this->assertEquals(3600.0, $data['ingredients'][0]['subtotal']);
        // Susu: 150 * 0.001 * 20.000 = 3.000
        $this->assertEquals(3000.0, $data['ingredients'][1]['subtotal']);
        // Cup: 1 * 1 * (25.000 / 50) = 500
        $this->assertEquals(500.0, $data['ingredients'][2]['subtotal']);

        // Total variable cost: 3600 + 3000 + 500 = 7.100
        $this->assertEquals(7100.0, $data['summary']['total_variable_cost']);
        // Base HPP: 7.100 + 1.000 (ops) = 8.100
        $this->assertEquals(8100.0, $data['summary']['base_hpp']);

        // Pricing Tiers exist
        $this->assertArrayHasKey('kompetitif', $data['pricing_tiers']);
        $this->assertArrayHasKey('standar', $data['pricing_tiers']);
        $this->assertArrayHasKey('premium', $data['pricing_tiers']);

        // Custom Analysis for price Rp 22.000
        $this->assertEquals(22000.0, $data['custom_analysis']['selling_price']);
        $this->assertEquals(13900.0, $data['custom_analysis']['profit']); // 22000 - 8100
        $this->assertTrue($data['custom_analysis']['is_healthy_margin']);
    }

    public function test_admin_can_create_product_with_ingredients_and_auto_sku()
    {
        Sanctum::actingAs($this->adminUser);

        $payload = [
            'name' => 'Caramel Latte Test',
            'category_id' => $this->coffeeCategory->id,
            'price' => 25000,
            'description' => 'Espresso dengan steamed milk dan saus karamel',
            'operational_cost' => 1200,
            'ingredients' => [
                [
                    'name' => 'Biji Kopi House Blend',
                    'amount' => 18,
                    'unit' => 'gram',
                    'buy_price' => 180000,
                    'buy_amount' => 1,
                    'buy_unit' => 'kg',
                ],
                [
                    'name' => 'Fresh Milk',
                    'amount' => 160,
                    'unit' => 'ml',
                    'buy_price' => 21000,
                    'buy_amount' => 1,
                    'buy_unit' => 'liter',
                ],
            ],
        ];

        $response = $this->postJson('/api/admin/products', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $createdProduct = $response->json('data');
        $this->assertEquals('Caramel Latte Test', $createdProduct['name']);
        $this->assertStringStartsWith('COF-', $createdProduct['sku']);
        $this->assertGreaterThan(0, $createdProduct['harga_beli']);

        // Verifikasi di Database
        $this->assertDatabaseHas('products', [
            'id' => $createdProduct['id'],
            'name' => 'Caramel Latte Test',
            'category_id' => $this->coffeeCategory->id,
        ]);

        $this->assertDatabaseHas('product_ingredients', [
            'product_id' => $createdProduct['id'],
            'name' => 'Biji Kopi House Blend',
        ]);
    }

    public function test_admin_can_create_product_with_image_upload()
    {
        Storage::fake('public');
        Sanctum::actingAs($this->adminUser);

        $image = UploadedFile::fake()->image('coffee_menu.jpg', 600, 600);

        $payload = [
            'name' => 'Manual Brew V60',
            'category_id' => $this->coffeeCategory->id,
            'price' => 28000,
            'image' => $image,
            'ingredients' => [
                [
                    'name' => 'Single Origin Beans',
                    'amount' => 15,
                    'unit' => 'gram',
                    'buy_price' => 300000,
                    'buy_amount' => 1,
                    'buy_unit' => 'kg',
                ],
                [
                    'name' => 'V60 Paper Filter',
                    'amount' => 1,
                    'unit' => 'pcs',
                    'buy_price' => 50000,
                    'buy_amount' => 100,
                    'buy_unit' => 'pcs',
                ],
            ],
        ];

        $response = $this->postJson('/api/admin/products', $payload);

        $response->assertStatus(201);
        $productId = $response->json('data.id');
        $imagePath = $response->json('data.image');

        $this->assertNotNull($imagePath);
        Storage::disk('public')->assertExists($imagePath);
    }

    public function test_admin_can_update_product_and_replace_ingredients()
    {
        Sanctum::actingAs($this->adminUser);

        // Buat produk awal
        $product = Product::create([
            'name' => 'Americano Hot Initial',
            'category_id' => $this->coffeeCategory->id,
            'sku' => 'COF-8899',
            'price' => 18000,
            'harga_beli' => 4000,
            'is_active' => true,
        ]);

        ProductIngredient::create([
            'product_id' => $product->id,
            'name' => 'Old Beans',
            'amount' => 15,
            'unit' => 'gram',
            'buy_price' => 150000,
            'buy_amount' => 1,
            'buy_unit' => 'kg',
            'subtotal' => 2250,
        ]);

        // Update produk dengan bahan baru dan harga baru
        $updatePayload = [
            'name' => 'Americano Hot Specialty',
            'price' => 20000,
            'operational_cost' => 800,
            'ingredients' => [
                [
                    'name' => 'Specialty Arabica Beans',
                    'amount' => 18,
                    'unit' => 'gram',
                    'buy_price' => 240000,
                    'buy_amount' => 1,
                    'buy_unit' => 'kg',
                ],
            ],
        ];

        $response = $this->putJson("/api/admin/products/{$product->id}", $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Americano Hot Specialty',
                    'price' => 20000,
                ],
            ]);

        // Verifikasi bahan lama sudah terhapus dan bahan baru tersimpan
        $this->assertDatabaseMissing('product_ingredients', [
            'product_id' => $product->id,
            'name' => 'Old Beans',
        ]);

        $this->assertDatabaseHas('product_ingredients', [
            'product_id' => $product->id,
            'name' => 'Specialty Arabica Beans',
        ]);
    }

    public function test_admin_can_soft_delete_product()
    {
        Sanctum::actingAs($this->adminUser);

        $product = Product::create([
            'name' => 'Menu to Delete',
            'category_id' => $this->coffeeCategory->id,
            'sku' => 'COF-DEL-01',
            'price' => 15000,
            'harga_beli' => 5000,
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/admin/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    public function test_handles_multipart_json_string_ingredients_gracefully()
    {
        Sanctum::actingAs($this->adminUser);

        // Simulasi mobile app mengirim array ingredients sebagai string JSON
        $ingredientsJson = json_encode([
            [
                'name' => 'Matcha Powder Premium',
                'amount' => 20,
                'unit' => 'gram',
                'buy_price' => 250000,
                'buy_amount' => 1,
                'buy_unit' => 'kg',
            ],
        ]);

        $payload = [
            'name' => 'Matcha Latte Ice',
            'category_id' => $this->coffeeCategory->id,
            'price' => 26000,
            'ingredients' => $ingredientsJson, // JSON string
        ];

        $response = $this->postJson('/api/admin/products', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('product_ingredients', [
            'name' => 'Matcha Powder Premium',
        ]);
    }

    public function test_zero_division_guard_on_zero_buy_amount()
    {
        Sanctum::actingAs($this->adminUser);

        $payload = [
            'ingredients' => [
                [
                    'name' => 'Zero Amount Bug Check',
                    'amount' => 10,
                    'unit' => 'gram',
                    'buy_price' => 50000,
                    'buy_amount' => 0, // Nilai 0 ditangani service dengan aman (fallback 1.0)
                    'buy_unit' => 'kg',
                ],
            ],
            'target_penjualan_bulanan' => 3000,
        ];

        $response = $this->postJson('/api/admin/hpp/calculate', $payload);

        // Tidak boleh crash 500
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_admin_can_get_hpp_summary_metrics()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/admin/hpp/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_active_products',
                    'average_margin_percent',
                    'average_food_cost_percent',
                    'is_margin_healthy',
                    'low_margin_count',
                    'low_margin_products',
                ],
            ]);
    }

    public function test_admin_can_get_categories_list()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/admin/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'products_count',
                    ],
                ],
            ]);
    }

    public function test_ai_recipe_endpoint_requires_product_name()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/admin/hpp/ai-recipe', []);
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }
}
