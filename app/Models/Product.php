<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    const DEFAULT_PAGINATION_PER_PAGE = 50;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    protected $with = [
        'stocks',
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

}
