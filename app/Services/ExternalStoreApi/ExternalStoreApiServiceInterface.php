<?php

namespace App\Services\ExternalStoreApi;

use App\Services\ExternalStoreApi\Product;

interface ExternalStoreApiServiceInterface
{
    /**
     * @param array $query
     * @return array<Product>
     */
    public function fetchProducts(?array $query = null): mixed;

    /**
     * @param integer $id
     * @return Product | null
     */
    public function fetchProduct(int $id): Product | null;
}