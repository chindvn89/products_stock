<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

/*
 *  This class is used to test the stock's API endpoints
 */
class StockEndpointsTest extends TestCase
{

    use DatabaseTransactions;

    protected $faker;
    protected $productData;

    public function setUp() : void
    {
        parent::setUp();
    }

    public function testAddBulkStocks()
    {
        $productsFile = new UploadedFile('tests/Data/primex-products-test.csv', 'products.csv', 'text/csv', null, $test = true);
        $response = $this->post('/api/products/bulk', [
            'file' => $productsFile,
        ]);
        $response->assertOk();
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
        $response = $this->post('/api/stocks/bulk', [
            'file' => $stocksFile,
        ]);
        $response->assertOk();
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
