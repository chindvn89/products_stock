<?php

namespace App\Services;

use App\Imports\StocksImport;
use App\Repositories\StockRepositoryInterface;
use Maatwebsite\Excel\Facades\Excel;

class StockService extends BaseService
{
    protected $stockRepository;

    public function __construct(
        StockRepositoryInterface $stockRepository
    )
    {
        $this->stockRepository = $stockRepository;
    }

    /**
     * Insert multiple stocks from a csv file input
     * @param file $csvFile A csv file is a list of stocks
     * @return boolean
     */
    public function insertBulk($csvFile)
    {
        Excel::import(app()->make(StocksImport::class), $csvFile);
        return true;
    }
}
