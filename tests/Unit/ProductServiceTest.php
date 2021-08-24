<?php

namespace Tests\Unit;

use Tests\TestCase;
use Faker\Factory as Faker;
use App\Models\Product;
use App\Models\Stock;
use App\Services\ProductService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/*
 *  This class is used to test the product's service/model
 */
class ProductServiceTest extends TestCase
{

    use DatabaseTransactions;

    protected $faker;
    protected $productData;
    protected $productService;

    public function setUp() : void
    {
        parent::setUp();
        $this->faker = Faker::create();
        $this->productData = [
            'code' => Str::random(10),
            'name' => $this->faker->name,
            'description' => Str::random(144),
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

    public function testAddAStockToAProduct()
    {
        $product = Product::factory()->create();
        $onHand = rand(1, 999);
        $stockData = Stock::factory()->makeOne([
            'on_hand' => $onHand,
            'production_date' => $this->faker->date(Stock::PRODUCTION_DATE_INPUT_FORMAT)
        ]);
        $productionDateInDatabase = Carbon::createFromFormat(Stock::PRODUCTION_DATE_INPUT_FORMAT, $stockData->production_date)->format(Stock::PRODUCTION_DATE_DATABASE_FORMAT);
        $product = $this->productService->addStock($product->code, $stockData->toArray());
        $this->assertEquals($product->stocks->count(), 1);
        $this->assertEquals($product->stocks[0]->name, $stockData->name);
        $this->assertEquals($product->stocks[0]->description, $stockData->description);
        $this->assertEquals($product->stocks[0]->production_date, $productionDateInDatabase);
    }

    public function testUpsertBulkProduct()
    {
        $file = new UploadedFile('tests/Data/primex-products-test.csv', 'products.csv', 'text/csv', null, $test = true);
        $this->productService->upsertBulk($file);
        $this->assertDatabaseHas('products', [
            'code' => '229113',
            'name' => 'B-ED PIZZLES BP',
            'description' => 'B-ED PIZZLES BP',
        ]);
        $this->assertDatabaseHas('products', [
            'code' => '214639',
            'name' => 'BF-COLD WASH TRIPE BP EU',
            'description' => 'BF-COLD WASH TRIPE BP EU',
        ]);
        $this->assertDatabaseHas('products', [
            'code' => '137513',
            'name' => 'THICK FLK LT CH T24',
            'description' => 'THICK FLK LT CH T24',
        ]);
        $this->assertDatabaseHas('products', [
            'code' => '138201',
            'name' => 'WAKANUI INSIDE VP ST CH',
            'description' => 'WAKANUI INSIDE VP ST CH',
        ]);
        $this->assertDatabaseHas('products', [
            'code' => '905957',
            'name' => 'CHK-THIGH FILLET FR SL',
            'description' => 'CHK-THIGH FILLET FR SL',
        ]);
    }
}
