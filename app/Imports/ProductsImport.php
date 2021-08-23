<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class ProductsImport implements ToModel, WithUpserts, WithChunkReading, WithBatchInserts, WithStartRow
{
    public function model(array $row)
    {
        return new Product([
            'code' => $row[0],
            'name' => $row[1],
            'description' => $row[2],
        ]);
    }

    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        return 'code';
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
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
