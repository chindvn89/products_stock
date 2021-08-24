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

    public function insertBulk($csvFile)
    {
        Excel::import(new StocksImport, $csvFile);

        return true;
    }
}
