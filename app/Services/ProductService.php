<?php

namespace App\Services;

use App\Imports\ProductsImport;
use App\Models\Product;
use App\Models\Stock;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\StockRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Maatwebsite\Excel\Facades\Excel;

class ProductService extends BaseService
{

    protected $productRepository;
    protected $stockRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        StockRepositoryInterface $stockRepository
    )
    {
        $this->modelClass = Product::class;
        $this->productRepository = $productRepository;
        $this->stockRepository = $stockRepository;
    }

    /**
     * Get list of products, with pagination, filters
     * @param array  $params To filter the list
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function list($params = []) : Paginator
    {
        return $this->productRepository->getAllPaging($params);
    }

    /**
     * Get detail of a product
     * @param string $code An unique string to identify a product
     * @param array  $params To filter the stocks of a product
     * @throws Exception 404 if product is not found
     * @return \App\Models\Product
     */
    public function get(string $code, $params = []) : Product
    {
        $product = $this->productRepository->get($code, $params);
        if (empty($product)) {
            abort(404, trans('product.errors.not_found'));
        }

        return $product;
    }

    /**
     * Add a new product
     * @param array  $params Data of the product
     * @throws Exception 400 - If the code of the new product is duplicated
     * @return \App\Models\Product
     */
    public function insert(array $params) : Product
    {
        $product = $this->productRepository->get($params['code']);
        if (!empty($product)) {
            abort(400, trans('product.errors.is_existed'));
        }

        $product = $this->modelClass::create($params);
        return $product;
    }

    /**
     * Update a product
     * @param string $code An unique string to identify a product
     * @param array  $params Date to update the product
     * @throws Exception 404 - If product is not found
     * @return \App\Models\Product
     */
    public function update(string $code,array $params) : Product
    {
        $product = $this->get($code);
        $product->update($params);
        return $product;
    }

    /**
     * Delete a product
     * @param string $code An unique string to identify a product
     * @throws Exception 404 - If product is not found
     * @return boolean
     */
    public function delete(string $code) : bool
    {
        $product = $this->get($code);
        $product->delete();
        return true;
    }

    /**
     * Insert/Update multiple products from a csv file input
     * @param file $csvFile A csv file is a list of products
     * @return boolean
     */
    public function upsertBulk($csvFile)
    {
        Excel::import(new ProductsImport, $csvFile);
        return true;
    }

    /**
     * Add a stock to a product
     * @param string $code An unique string to identify a product
     * @param array $stockParams    Data of the new stock will be added to the product
     * @throws Exception 404 - If product is not found
     * @return boolean
     */
    public function addStock(string $code, array $stockParams)
    {
        $product = $this->get($code);
        $stockParams['product_id'] = $product->id;
        $stockParams['production_date'] = !empty($stockParams['production_date']) ? Carbon::createFromFormat(Stock::PRODUCTION_DATE_INPUT_FORMAT, $stockParams['production_date']) : NULL;
        $this->stockRepository->create($stockParams);
        $product = $this->get($code);
        return $product;
    }

}
