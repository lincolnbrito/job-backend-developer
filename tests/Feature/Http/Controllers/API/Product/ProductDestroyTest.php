<?php

namespace Tests\Feature\Http\Controllers\API\Product;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductDestroyTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $faker;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_should_not_delete_an_inexistent_product()
    {
        $response = $this->deleteJson(
            route('api.products.update', ['product' => 1], false), 
            []
        );
        
        $response->assertNotFound();

        $this->assertDatabaseCount('products', 0);
    }
}
