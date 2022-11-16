<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_should_not_found_a_product()
    {
        $response = $this->getJson(route('api.products.show', ['product' => 1], false));
        $response->assertNotFound();
    }

    public function test_it_should_find_a_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson(route('api.products.show', ['product' => $product->id], false));
        
        $response->assertOk()
            ->assertJson($product->toArray());
    }
}
