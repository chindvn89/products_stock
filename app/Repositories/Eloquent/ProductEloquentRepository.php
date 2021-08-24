<?php
namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\Stock;
use App\Repositories\ProductRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class ProductEloquentRepository extends EloquentRepository implements ProductRepositoryInterface
{

    /**
     * get model
     * @return string
     */
    public function getModel() : string
    {
        return Product::class;
    }

    /**
     * Get list of products
     * @param array $params Filters for products
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function getAllPaging($params = []) : Paginator
    {
        $query = $this->buildQueryList($params);
        $perPage = !empty($params['per_page']) ? (int)$params['per_page'] : Product::DEFAULT_PAGINATION_PER_PAGE;
        return $query->paginate($perPage);
    }

    /**
     * Get list of products
     * @param array $params Filters for products
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll($params = []) : Collection
    {
        $query = $this->buildQueryList($params);
        return $query->get();
    }

    /**
     * Get a product by code
     * @param string $code  An unique string to identify a product
     * @param array $params Filters for list stocks of the product
     * @return \App\Models\Product | null
     */
    public function get(string $code, $params = []) : ?Product
    {
        $query = $this->buildQueryGet($code, $params);
        $product = $query->first();
        return $product;
    }

    ////// private functions //////////

    /**
     * Build a query to get a product's detail
     * @param string $code  An unique string to identify a product
     * @param array $params Filters for list stocks of the product
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQueryGet($code, $params = [])
    {
        $query = $this->_model::where([
            'code' => $code,
        ]);
        $this->buildQueryAddSumOnHand($query, $params);

        return $query;
    }

    /**
     * Build a query to get list of products
     * @param array $params Filters for products
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQueryList($params = [])
    {
        $query = $this->_model::query();
        $this->buildQueryAddSumOnHand($query, $params); // always do it -> stocks_sum_on_hand is available for other steps
        $this->buildQueryFilter($query, $params);
        $this->buildQueryListOrder($query, $params);
        return $query;
    }

    /**
     * Modify a query, append data stocks_sum_on_hand to each product record
     * @param \Illuminate\Database\Eloquent\Builder &$query The query is modified
     * @param array $params Filters for products
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQueryAddSumOnHand(&$query, $params = [])
    {
        list($fromDate, $toDate) = $this->getDateFromParams($params);
        $query->withSum(['stocks' => function($q) use ($fromDate, $toDate) {
            if ($fromDate) {
                $q->whereDate('production_date', '>=', $fromDate);
            }
            if ($toDate) {
                $q->whereDate('production_date', '<=', $toDate);
            }
        }], 'on_hand');
    }

    /**
     * Modify a query, to apply filters to products
     * @param \Illuminate\Database\Eloquent\Builder &$query The query is modified
     * @param array $params Filters for products
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQueryFilter(&$query, $params = [])
    {
        // filter by production_date of stock
        list($fromDate, $toDate) = $this->getDateFromParams($params);
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
        // filter by codes
        if (!empty($params['codes']) && is_array($params['codes'])) {
            $query->whereIn('code', $params['codes']);
        }
    }

    /**
     * Modify a query, to order by something is input
     * @param \Illuminate\Database\Eloquent\Builder &$query The query is modified
     * @param array $params Filters for products
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQueryListOrder(&$query, $params = [])
    {
        if (!empty($params['sort']) && !empty($params['sort']['sum_on_hand'])) {
            $query->orderBy('stocks_sum_on_hand', $params['sort']['sum_on_hand']);
        }
    }

    /**
     * Get and standardize the dates from query params
     * @param array $params List query params
     * @return array [$fromDate, $toDate] Return list of dates with the correct format to build database query
     */
    public function getDateFromParams($params = [])
    {
        $fromDate = !empty($params['production_date_from']) ? Carbon::createFromFormat(Stock::PRODUCTION_DATE_INPUT_FORMAT, $params['production_date_from']) : NULL;
        $toDate = !empty($params['production_date_to']) ? Carbon::createFromFormat(Stock::PRODUCTION_DATE_INPUT_FORMAT, $params['production_date_to']) : NULL;
        return [
            $fromDate,
            $toDate,
        ];
    }

}
