<?php

namespace App\Services;

use App\Imports\ProductsImport;
use App\Models\Product;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductService extends BaseService
{

    public function __construct()
    {
        $this->modelClass = Product::class;
    }

    public function findAll($params = [])
    {
        $query = $this->buildQueryList($params);
        $perPage = !empty($params['per_page']) ? (int)$params['per_page'] : Product::DEFAULT_PAGINATION_PER_PAGE;
        return $query->paginate($perPage);
    }

    public function get(string $code, $throwException = false, $params = []) : ?Product
    {
        $query = $this->buildQueryGet($code, $params);
        $product = $query->first();

        if ($throwException && empty($product)) {
            abort(404, trans('product.errors.not_found'));
        }

        return $product;
    }

    public function insert(array $params) : Product
    {
        $product = $this->get($params['code']);
        if (!empty($product)) {
            abort(400, trans('product.errors.is_existed'));
        }

        $product = $this->modelClass::create($params);
        return $product;
    }

    public function update(string $code,array $params) : Product
    {
        $product = $this->get($code, true);
        $product->update($params);
        return $product;
    }

    public function delete(string $code) : bool
    {
        $product = $this->get($code, true);
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
        $product = $this->get($code, true);
        $stockParams['product_id'] = $product->id;
        $stockParams['production_date'] = !empty($stockParams['production_date']) ? Carbon::createFromFormat(Stock::PRODUCTION_DATE_INPUT_FORMAT, $stockParams['production_date']) : NULL;
        Stock::create($stockParams);
        $product = $this->get($code, true);
        return $product;
    }

    /////// private functions //////////

    private function buildQueryGet($code, $params = [])
    {
        $query = $this->modelClass::where([
            'code' => $code,
        ]);
        $this->buildQueryAddSumOnHand($query, $params);

        return $query;
    }

    private function buildQueryList($params = [])
    {
        $query = $this->modelClass::query();
        $this->buildQueryAddSumOnHand($query, $params); // always do it -> stocks_sum_on_hand is available for other steps
        $this->buildQueryFilter($query, $params);
        $this->buildQueryListOrder($query, $params);
        return $query;
    }

    private function buildQueryAddSumOnHand(&$query, $params = [])
    {
        list($fromDate, $toDate) = $this->buildQueryGetDateFromParams($params);
        $query->withSum(['stocks' => function($q) use ($fromDate, $toDate) {
            if ($fromDate) {
                $q->whereDate('production_date', '>=', $fromDate);
            }
            if ($toDate) {
                $q->whereDate('production_date', '<=', $toDate);
            }
        }], 'on_hand');
    }

    private function buildQueryFilter(&$query, $params = [])
    {
        // filter by production_date of stock
        list($fromDate, $toDate) = $this->buildQueryGetDateFromParams($params);
        if ($fromDate) {
            $query->whereHas('stocks', function($q) use ($fromDate) {
                $q->whereDate('production_date', '>=', $fromDate);
            });
        }
        if ($toDate) {
            $query->whereHas('stocks', function($q) use ($toDate) {
                $q->whereDate('production_date', '<=', $toDate);
            });
        }
        // filter by sum(on_hand) of stock
        if (!empty($params['sum_on_hand_min'])) {
            $query->having('stocks_sum_on_hand', '>=', (int)$params['sum_on_hand_min']);
        }
        if (!empty($params['sum_on_hand_max'])) {
            $query->having('stocks_sum_on_hand', '<=', (int)$params['sum_on_hand_max']);
        }
    }

    private function buildQueryListOrder(&$query, $params = [])
    {
        if (!empty($params['sort']) && !empty($params['sort']['sum_on_hand'])) {
            $query->orderBy('stocks_sum_on_hand', $params['sort']['sum_on_hand']);
        }
    }

    public function buildQueryGetDateFromParams($params = [])
    {
        $fromDate = !empty($params['production_date_from']) ? Carbon::createFromFormat(Stock::PRODUCTION_DATE_INPUT_FORMAT, $params['production_date_from']) : NULL;
        $toDate = !empty($params['production_date_to']) ? Carbon::createFromFormat(Stock::PRODUCTION_DATE_INPUT_FORMAT, $params['production_date_to']) : NULL;
        return [
            $fromDate,
            $toDate,
        ];
    }
}
