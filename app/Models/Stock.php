<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'stock';

    const PRODUCTION_DATE_FORMAT = 'd/m/Y';

    protected $fillable = [
        'product_id',
        'on_hand', // integer: on_hand > 0 (input), on_hand < 0 (output)
        'taken', // boolean
        'production_date',
    ];

    protected $casts = [
        'taken' => 'bool'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
