<?php

namespace Tests\Unit;

use Tests\TestCase;
use Faker\Factory as Faker;
use App\Models\Product;
use App\Models\Stock;
use App\Services\ProductService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/*
 *  This class is used to test the stock's service/model
 */
class StockServiceTest extends TestCase
{

    use DatabaseTransactions;

    protected $faker;
    protected $productData;
    protected $productService;
    protected $stockService;

    public function setUp() : void
    {
        parent::setUp();
        $this->faker = Faker::create();
        $this->productService = app()->make(ProductService::class);
        $this->stockService = app()->make(StockService::class);
    }

    public function testInsertBulkStocks()
    {
        $productsFile = new UploadedFile('tests/Data/primex-products-test.csv', 'products.csv', 'text/csv', null, $test = true);
        $this->productService->upsertBulk($productsFile);
        $product1 = Product::where(['code' => '382026'])->first();
        $product2 = Product::where(['code' => '49354'])->first();
        $product3 = Product::where(['code' => '905900'])->first();
        $product4 = Product::where(['code' => '905906'])->first();
        $product5 = Product::where(['code' => '69806'])->first();
        $this->assertNotEmpty($product1);
        $this->assertNotEmpty($product2);
        $this->assertNotEmpty($product3);
        $this->assertNotEmpty($product4);
        $this->assertNotEmpty($product5);

        $stocksFile = new UploadedFile('tests/Data/primex-stock-test.csv', 'stocks.csv', 'text/csv', null, $test = true);
        $this->stockService->insertBulk($stocksFile);
        $this->assertDatabaseHas('stock', [
            'product_id' => $product1->id,
            'on_hand' => 1,
            'production_date' => '2020-08-26',
        ]);
        $this->assertDatabaseHas('stock', [
            'product_id' => $product2->id,
            'on_hand' => 1,
            'production_date' => '2020-07-29',
        ]);
        $this->assertDatabaseHas('stock', [
            'product_id' => $product3->id,
            'on_hand' => 54,
            'production_date' => '2019-11-13',
        ]);
        $this->assertDatabaseHas('stock', [
            'product_id' => $product4->id,
            'on_hand' => 35,
            'production_date' => '2020-04-18',
        ]);
        $this->assertDatabaseHas('stock', [
            'product_id' => $product5->id,
            'on_hand' => 50,
            'production_date' => '2020-07-21',
        ]);
    }
}
