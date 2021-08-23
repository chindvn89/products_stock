<?php
namespace App\Transformers;

use App\Models\Product;
use League\Fractal\TransformerAbstract;

class ProductTransformer extends TransformerAbstract
{
    public function transform(Product $product)
    {
        $productData = [
            'id'   => $product['id'],
            'code'   => $product['code'],
            'name' => $product['name'],
            'description' => $product['description'],
            'sum_on_hand' => !empty($product['stocks_sum_on_hand']) ? $product['stocks_sum_on_hand'] : 0,
        ];

        return $productData;
    }
}
