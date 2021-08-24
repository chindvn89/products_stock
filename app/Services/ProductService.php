<?php

namespace App\Services;

use App\Imports\ProductsImport;
use App\Models\Product;
use App\Models\Stock;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\StockRepositoryInterface;
use Carbon\Carbon;
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

    public function list($params = [])
    {
        return $this->productRepository->getAllPaging($params);
    }

    public function get(string $code, $params = []) : ?Product
    {
        $product = $this->productRepository->get($code, $params);
        if (empty($product)) {
            abort(404, trans('product.errors.not_found'));
        }

        return $product;
    }

    public function insert(array $params) : Product
    {
        $product = $this->productRepository->get($params['code']);
        if (!empty($product)) {
            abort(400, trans('product.errors.is_existed'));
        }

        $product = $this->modelClass::create($params);
        return $product;
    }

    public function update(string $code,array $params) : Product
    {
        $product = $this->get($code);
        $product->update($params);
        return $product;
    }

    public function delete(string $code) : bool
    {
        $product = $this->get($code);
        $product->delete();
        return true;
    }

    public function upsertBulk($csvFile)
    {
        Excel::import(new ProductsImport, $csvFile);
        return true;
    }

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
