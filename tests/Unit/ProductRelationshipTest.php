<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/*
 *  This class is used to test the product's service/model
 */
class ProductRelastionshipTest extends TestCase
{

    use DatabaseTransactions;

    public function setUp() : void
    {
        parent::setUp();
    }

    public function testProductHasManyStocks()
    {
        $newProduct = Product::factory()->create();
        $newStocks = Stock::factory()->count(5)->create(['product_id' => $newProduct->id]);
        $this->assertInstanceOf(Product::class, $newStocks[0]->product);
        $this->assertEquals(5, $newProduct->stocks->count());
    }

}
