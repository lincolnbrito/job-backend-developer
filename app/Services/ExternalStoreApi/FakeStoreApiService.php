<?php

namespace App\Services\ExternalStoreApi;

use App\Services\ExternalStoreApi\Product;
use Illuminate\Support\Facades\Http;

class FakeStoreApiService implements ExternalStoreApiServiceInterface 
{
    protected $client;

    public function __construct(){
        $this->client = Http::baseUrl(config('services.external_store.endpoint'))
            ->acceptJson()
            ->contentType('application/json');
    }

    /**
     * @param array $query
     * @return array<Product>
     */
    public function fetchProducts(?array $query = null): mixed 
    {
        $response = $this->client->get('/products', $query);

        if ($response->failed()) {
            throw new \Exception('Could not fetch products');
        }
       
        return array_map(function($product) {
            return new Product($product);
        }, $response->json());
       
    }

    /**
     * @param integer $id
     * @return Product | null
     */
    public function fetchProduct(int $id): Product | null
    {
        $response = $this->client->get("/products/$id");

        if ($response->failed()) {
            throw new \Exception('Could not fetch product');
        }

        if ($response->json() == null) {
            throw new \Exception('Product not found');
        }
        
        $productData = $response->json();

        return new Product($productData);;
    }

}