<?php

namespace App\Imports;

use App\Repositories\ProductRepositoryInterface;
use App\Repositories\StockRepositoryInterface;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

class StocksImport implements ToArray, WithChunkReading, WithStartRow
{
    protected $stockRepository;
    protected $productRepository;

    public function __construct(
        StockRepositoryInterface $stockRepository,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->stockRepository = $stockRepository;
        $this->productRepository = $productRepository;
    }

    public function array(array $rows)
    {
        $stocksInput = [];
        foreach($rows as $row) {
            $stocksInput[] = [
                'code' => $row[0],
                'on_hand' => $row[1],
                'production_date' => $row[2],
            ];
        }

        $codes = array_unique(array_column($stocksInput, 'code'));
        $products = $this->productRepository->getAll(['codes' => $codes])->keyBy('code');
        $standardData = [];
        for ($i=0; $i < count($stocksInput); $i++) {
            $stock = $stocksInput[$i];
            $code = $stock['code'];
            if (!$products->has($code)) {
                continue;
            }

            unset($stock['code']);
            $stock['product_id'] = $products[$code]->id;
            $stock['production_date'] = Carbon::createFromFormat('d/m/Y', $stock['production_date']);
            $stock['created_at'] = Carbon::now();
            $stock['updated_at'] = Carbon::now();
            $standardData[] = $stock;
        }

        $this->stockRepository->insertBulk($standardData);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

     /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }
}
