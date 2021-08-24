<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\Paginator;

interface ProductRepositoryInterface extends RepositoryInterface
{
    /**
     * Get list of products
     * @param array $params Filters for products
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    function getAllPaging(array $params) : Paginator;

    /**
     * Get a product by code
     * @param string $code  An unique string to identify a product
     * @return \App\Models\Product | null
     */
    function get(string $code);
}
