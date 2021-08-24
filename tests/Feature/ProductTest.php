<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Faker\Factory as Faker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProductTest extends TestCase
{

    use DatabaseTransactions;

    protected $faker;
    protected $productData;

    public function setUp() : void
    {
        parent::setUp();
        $this->faker = Faker::create();
        $this->productData = [
            'code' => rand(1000000,9999999),
            'name' => $this->faker->name,
            'description' => Str::random(144),
        ];
    }

    public function testGetListEndpoint()
    {
        Product::factory()->count(115)->create();
        $response = $this->get('/api/products?page=2&per_page=35');
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'code',
                    'name',
                    'description',
                    'sum_on_hand'
                ]
            ],
            'meta' => [
                'pagination' => [
                    'total',
                    'per_page',
                    'current_page',
                ]
            ],
        ]);
        $response->assertJsonPath('meta.pagination.per_page', 35);
        $response->assertJsonPath('meta.pagination.current_page', 2);
    }

    public function testGetDetailEndpoint()
    {
        $product = Product::factory()->create();
        $stock1 = Stock::factory()->create([
            'product_id' => $product->id,
            'on_hand' => 14,
            'production_date' => '2021-12-24',
        ]);
        $stock2 = Stock::factory()->create([
            'product_id' => $product->id,
            'on_hand' => -3,
            'production_date' => '2021-12-01',
        ]);
        $stock3 = Stock::factory()->create([
            'product_id' => $product->id,
            'on_hand' => 20,
            'production_date' => '2020-12-10',
        ]);
        $stock4 = Stock::factory()->create([
            'product_id' => $product->id,
            'on_hand' => -2,
            'production_date' => '2021-11-10',
        ]);
        $response = $this->get("/api/products/{$product->code}?production_date_from=11/10/2021");
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'code',
                'name',
                'description',
                'sum_on_hand'
            ]
        ]);
        $responseContent = $response->decodeResponseJson($response->getContent());
        $this->assertEquals($responseContent['data']['code'], $product->code);
        $this->assertEquals($responseContent['data']['name'], $product->name);
        $this->assertEquals($responseContent['data']['description'], $product->description);
        $this->assertEquals($responseContent['data']['sum_on_hand'], ($stock1->on_hand + $stock2->on_hand + $stock4->on_hand));
    }

    public function testAddAProductEndpoint()
    {
        $product = Product::factory()->makeOne();
        $response = $this->post('/api/products', $product->toArray());
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'code',
                'name',
                'description',
                'sum_on_hand'
            ]
        ]);
        $responseContent = $response->decodeResponseJson($response->getContent());
        $this->assertEquals($responseContent['data']['code'], $product->code);
        $this->assertEquals($responseContent['data']['name'], $product->name);
        $this->assertEquals($responseContent['data']['description'], $product->description);
        $this->assertEquals($responseContent['data']['sum_on_hand'], 0);
    }

    public function testAddAStockForAProductEndpoint()
    {
        $product = Product::factory()->create();
        $onHand = rand(1, 999);
        $stock = Stock::factory()->makeOne([
            'on_hand' => $onHand,
            'production_date' => $this->faker->date(Stock::PRODUCTION_DATE_INPUT_FORMAT)
        ]);
        $response = $this->post("/api/products/{$product->code}/stocks", $stock->toArray());
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'code',
                'name',
                'description',
                'sum_on_hand'
            ]
        ]);
        $responseContent = $response->decodeResponseJson($response->getContent());
        $this->assertEquals($responseContent['data']['code'], $product->code);
        $this->assertEquals($responseContent['data']['name'], $product->name);
        $this->assertEquals($responseContent['data']['description'], $product->description);
        $this->assertEquals($responseContent['data']['sum_on_hand'], $onHand);
        $this->assertEquals($product->stocks->count(), 1);
    }

    public function testUpdateAProductEndpoint()
    {
        $product = Product::factory()->create();
        $updateData = [
            'name' => $this->productData['name'],
            'description' => $this->productData['description'],
        ];
        $response = $this->put("/api/products/{$product->code}", $updateData);
        $response->assertOk();
        $responseContent = $response->decodeResponseJson($response->getContent());
        $this->assertEquals($responseContent['data']['code'], $product->code);
        $this->assertEquals($responseContent['data']['name'], $updateData['name']);
        $this->assertEquals($responseContent['data']['description'], $updateData['description']);
    }

    public function testDeleteAProductEndpoint()
    {
        $product = Product::factory()->create();
        $response = $this->delete("/api/products/{$product->code}");
        $response->assertOk();
        $this->assertSoftDeleted('products', [
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'description' => $product->description,
        ]);
    }

    public function testUpsertBulkProductsEndpoint()
    {
        $file = new UploadedFile('tests/Data/primex-products-test.csv', 'products.csv', 'text/csv', null, $test = true);
        $response = $this->post('/api/products/bulk', [
            'file' => $file,
        ]);
        $response->assertOk();
        $this->assertDatabaseHas('products', [
            'code' => '229113',
            'name' => 'B-ED PIZZLES BP',
            'description' => 'B-ED PIZZLES BP',
        ]);
        $this->assertDatabaseHas('products', [
            'code' => '137513',
            'name' => 'THICK FLK LT CH T24',
            'description' => 'THICK FLK LT CH T24',
        ]);
        $this->assertDatabaseHas('products', [
            'code' => '137513',
            'name' => 'THICK FLK LT CH T24',
            'description' => 'THICK FLK LT CH T24',
        ]);
        $this->assertDatabaseHas('products', [
            'code' => '65610',
            'name' => 'PS-TDR STEAK PIECES VP CH',
            'description' => 'PS-TDR STEAK PIECES VP CH',
        ]);
        $this->assertDatabaseHas('products', [
            'code' => '905957',
            'name' => 'CHK-THIGH FILLET FR SL',
            'description' => 'CHK-THIGH FILLET FR SL',
        ]);
    }

    public function testAddBulkStocksEndpoint()
    {
        $file = new UploadedFile('tests/Data/primex-products-test.csv', 'products.csv', 'text/csv', null, $test = true);
        $response = $this->post('/api/products/bulk', [
            'file' => $file,
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

        $file = new UploadedFile('tests/Data/primex-stock-test.csv', 'stocks.csv', 'text/csv', null, $test = true);
        $response = $this->post('/api/stocks/bulk', [
            'file' => $file,
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
