<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\AddProductRequest;
use App\Http\Requests\Product\AddProductStockRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\UpsertProductBulkRequest;
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

    /**
     * Get list of products, with pagination, filters
     * @param Illuminate\Http\Request   $request
     * @return \Dingo\Api\Http\Response
     */
    public function index(Request $request)
    {
        $products = $this->productService->list($request->all());
        return $this->response->paginator($products, new ProductTransformer());
    }

    /**
     * Add a new product
     * @param App\Http\Requests\Product\AddProductRequest   $request
     * @return \Dingo\Api\Http\Response
     */
    public function store(AddProductRequest $request)
    {
        $product = $this->productService->insert($request->all());
        return $this->response->item($product, new ProductTransformer());
    }

    /**
     * Get detail of a product, can use filters
     * @param Illuminate\Http\Request   $request
     * @param string   $code    An unique string to identify a product
     * @return \Dingo\Api\Http\Response
     */
    public function show(Request $request, string $code)
    {
        $product = $this->productService->get($code, $request->all());
        return $this->response->item($product, new ProductTransformer());
    }

    /**
     * Update a product
     * @param App\Http\Requests\Product\UpdateProductRequest   $request
     * @param string   $code    An unique string to identify a product
     * @return \Dingo\Api\Http\Response
     */
    public function update(UpdateProductRequest $request, string $code)
    {
        $product = $this->productService->update($code, $request->only([
            'name',
            'description',
        ]));
        return $this->response->item($product, new ProductTransformer());
    }

    /**
     * Delete a product
     * @param string   $code    An unique string to identify a product
     * @return \Dingo\Api\Http\Response
     */
    public function destroy($code)
    {
        $result = $this->productService->delete($code);
        return $this->response->array([
            'succeed' => $result,
        ]);
    }

    /**
     * Insert/Update multiple products by posting a csv file
     * @param App\Http\Requests\Product\UpsertProductBulkRequest   $request
     * @return \Dingo\Api\Http\Response
     */
    public function upsertBulk(UpsertProductBulkRequest $request)
    {
        $result = $this->productService->upsertBulk($request->file('file'));
        return $this->response->array([
            'succeed' => $result,
        ]);
    }

    /**
     * Add a stock to a product
     * @param App\Http\Requests\Product\AddProductStockRequest   $request
     * @param string   $code    An unique string to identify a product
     * @return \Dingo\Api\Http\Response
     */
    public function addStock(AddProductStockRequest $request, $code)
    {
        $product = $this->productService->addStock($code, $request->all());
        return $this->response->item($product, new ProductTransformer());
    }
}
