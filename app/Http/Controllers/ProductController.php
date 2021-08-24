<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\AddProductRequest;
use App\Http\Requests\Product\AddProductStockRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\UpsertProductBulkRequest;
use App\Models\Product;
use App\Services\ProductService;
use App\Transformers\ProductTransformer;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;

class ProductController extends Controller
{

    use Helpers;

    private $productService;

    public function __construct(
        ProductService $productService
    )
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $products = $this->productService->list($request->all());
        return $this->response->paginator($products, new ProductTransformer());
    }

    public function store(AddProductRequest $request)
    {
        $product = $this->productService->insert($request->all());
        return $this->response->item($product, new ProductTransformer());
    }

    public function show(Request $request, string $code)
    {
        $product = $this->productService->get($code, $request->all());
        return $this->response->item($product, new ProductTransformer());
    }

    public function update(UpdateProductRequest $request, string $code)
    {
        $product = $this->productService->update($code, $request->only([
            'name',
            'description',
        ]));
        return $this->response->item($product, new ProductTransformer());
    }

    public function destroy($code)
    {
        $result = $this->productService->delete($code);
        return $this->response->array([
            'succeed' => $result,
        ]);
    }

    public function upsertBulk(UpsertProductBulkRequest $request)
    {
        $result = $this->productService->upsertBulk($request->file('file'));
        return $this->response->array([
            'succeed' => $result,
        ]);
    }

    public function addStock(AddProductStockRequest $request, $code)
    {
        $product = $this->productService->addStock($code, $request->all());
        return $this->response->item($product, new ProductTransformer());
    }
}
