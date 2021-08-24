<?php

namespace Tests\Unit;

use Tests\TestCase;
use Faker\Factory as Faker;
use App\Models\Product;
use App\Models\Stock;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductTest extends TestCase
{

    use DatabaseTransactions;

    protected $productData;
    protected $productService;

    public function setUp() : void
    {
        parent::setUp();
        $this->faker = Faker::create();
        $this->productData = [
            'code' => $this->faker->name,
            'name' => $this->faker->name,
            'description' => $this->faker->realText(rand(10, 200)),
        ];
        $this->productService = app()->make(ProductService::class);
    }

    public function testInsert()
    {
        $product = $this->productService->insert($this->productData);
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals($this->productData['code'], $product->code);
        $this->assertEquals($this->productData['name'], $product->name);
        $this->assertEquals($this->productData['description'], $product->description);
        $this->assertDatabaseHas('products', $this->productData);
    }

    public function testGet()
    {
        $newProduct = Product::factory()->create();
        $getProduct = $this->productService->get($newProduct->code);
        $this->assertInstanceOf(Product::class, $getProduct);
        $this->assertEquals($getProduct->name, $newProduct->name);
        $this->assertEquals($getProduct->description, $newProduct->description);
    }

    public function testUpdate()
    {
        $newProduct = Product::factory()->create();
        $updatedProduct = $this->productService->update($newProduct->code, $this->productData);
        // Kiểm tra dữ liệu trả về
        $this->assertInstanceOf(Product::class, $updatedProduct);
        $this->assertEquals($updatedProduct->name, $this->productData['name']);
        $this->assertEquals($updatedProduct->description, $this->productData['description']);
        $this->assertDatabaseHas('products', $this->productData);
    }

    public function testDestroy()
    {
        $newProduct = Product::factory()->create();
        $deleteResult = $this->productService->delete($newProduct->code);
        $this->assertTrue($deleteResult);
        $this->assertSoftDeleted('products', [
            'id' => $newProduct->id,
            'code' => $newProduct->code,
            'name' => $newProduct->name,
            'description' => $newProduct->description,
        ]);
    }

    public function testProductHasManyStocks()
    {
        $newProduct = Product::factory()->create();
        $newStocks = Stock::factory()->count(5)->create(['product_id' => $newProduct->id]);
        $this->assertInstanceOf(Product::class, $newStocks[0]->product);
        $this->assertEquals(5, $newProduct->stocks->count());
    }
}
