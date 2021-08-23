<?php

namespace App\Services;

use App\Imports\StocksImport;
use App\Models\Product;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class StockService extends BaseService
{

    public function __construct()
    {
        $this->modelClass = Stock::class;
    }

    public function insertBulk($csvFile)
    {
        Excel::import(new StocksImport, $csvFile);

        return true;
    }
}
