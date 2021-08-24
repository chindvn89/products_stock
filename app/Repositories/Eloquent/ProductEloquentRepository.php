<?php
namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\Stock;
use App\Repositories\ProductRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;

class ProductEloquentRepository extends EloquentRepository implements ProductRepositoryInterface
{

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return Product::class;
    }

    public function getAll($params = []) : Paginator
    {
        $query = $this->buildQueryList($params);
        $perPage = !empty($params['per_page']) ? (int)$params['per_page'] : Product::DEFAULT_PAGINATION_PER_PAGE;
        return $query->paginate($perPage);
    }

    public function get(string $code, $params = []) : ?Product
    {
        $query = $this->buildQueryGet($code, $params);
        $product = $query->first();
        return $product;
    }


    ////// private functions //////////

    private function buildQueryGet($code, $params = [])
    {
        $query = $this->_model::where([
            'code' => $code,
        ]);
        $this->buildQueryAddSumOnHand($query, $params);

        return $query;
    }

    private function buildQueryList($params = [])
    {
        $query = $this->_model::query();
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
