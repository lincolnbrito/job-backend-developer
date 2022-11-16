<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class ProductStoreTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $faker;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_should_create_product()
    {
        $productData = Product::factory()->make()->toArray();

        $response = $this->postJson(route('api.products.store', [], false), $productData);
        $response->assertCreated();

        $this->assertDatabaseHas('products', $productData);
        $this->assertDatabaseCount('products', 1);
    }

    /**
     * @dataProvider provideInvalidProducts
     *
     * @return void
     */
    public function test_it_should_not_create_invalid_product(
        $productData,
        $expectedErrors
    )
    {
        $response = $this->postJson(route('api.products.store', [], false), $productData);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors($expectedErrors)
            ->assertJsonCount(count($expectedErrors), 'errors');
    }

    public function test_it_should_not_create_product_with_duplicated_name()
    {
        $oldProduct = Product::factory()->create();
        
        $newProductData = Product::factory()->make([
            'name' => $oldProduct->name
        ])->toArray();

        $response = $this->postJson(route('api.products.store', [], false), $newProductData);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonCount(1, 'errors');
    }

    public function provideInvalidProducts()
    {
        return [
            [
                [], ['name', 'price', 'description', 'category']
            ],
            [
                ['name' => 'fake product'],
                [ 
                    'price', 
                    'description', 
                    'category' 
                ]
            ],
            [
                [
                    'name' => 'fake product',
                    'description' => 'lorem ipsum',
                    'price' => 'INVALID',
                    'category' => 'category #1'
                ],
                [  
                    'price',
                ]
            ],
            [
                ['image_url' => 'FAKE_URL'],
                [  
                    'name',
                    'price', 
                    'description', 
                    'category',
                    'image_url'
                ]
            ]

        ];
    }
}
