<?php

namespace Tests\Browser;

use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProductE2ETest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testProductList()
    {
        $products = Product::factory()->count(10)->create();
        $this->browse(function (Browser $browser) use ($products) {
            $browser->visit('/api/products?sort[sum_on_hand]=desc&per_page=30')
                ->assertSee('"code"')
                ->assertSee($products[0]->code)
                ->assertSee($products[9]->name)
                ->assertSee($products[3]->description)
                ->assertSee($products[7]->code)
                ->assertSee('current_page')
                ->assertSee('total')
                ->assertSee('total_pages');
        });
    }

    public function testProductDetail()
    {
        $products = Product::factory()->count(5)->create();

        $this->browse(function (Browser $browser) use ($products) {
            $browser->visit("/api/products/{$products[0]->code}")
                ->assertSee("{$products[0]->name}")
                ->assertSee("{$products[0]->description}")
                ->assertSee('sum_on_hand');

            $browser->visit("/api/products/{$products[2]->code}")
                ->assertSee("{$products[2]->name}")
                ->assertSee("{$products[2]->description}")
                ->assertSee('sum_on_hand');

            $browser->visit("/api/products/{$products[4]->code}")
                ->assertSee("{$products[4]->name}")
                ->assertSee("{$products[4]->description}")
                ->assertSee('sum_on_hand');

        });
    }

}
