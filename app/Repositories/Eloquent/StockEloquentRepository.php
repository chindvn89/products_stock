<?php
namespace App\Repositories\Eloquent;

use App\Models\Stock;
use App\Repositories\StockRepositoryInterface;

class StockEloquentRepository extends EloquentRepository implements StockRepositoryInterface
{

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return Stock::class;
    }
}
