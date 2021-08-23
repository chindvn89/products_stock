<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\AddStockBulkRequest;
use App\Services\StockService;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;

class StockController extends Controller
{

    use Helpers;

    private $stockService;

    public function __construct(
        StockService $stockService
    )
    {
        $this->stockService = $stockService;
    }

    public function insertBulk(AddStockBulkRequest $request)
    {
        $result = $this->stockService->insertBulk($request->file('file'));
        return $this->response->array([
            'succeed' => $result,
        ]);
    }
}
