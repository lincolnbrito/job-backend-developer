<?php

namespace Tests\Feature\Http\Controllers\API\Product;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class ProductUpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $faker;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_should_not_update_an_inexistent_product()
    {
        $response = $this->putJson(
            route('api.products.update', ['product' => 1], false), 
            []
        );
        
        $response->assertNotFound();

        $this->assertDatabaseCount('products', 0);
    }

    /**
     * @dataProvider provideInvalidProducts
     */
    public function test_it_should_not_update_a_product_with_invalid_data(
        $productData,
        $expectedErrors,
    )
    {   
        $product = Product::factory()->create();

        $response = $this->putJson(
            route('api.products.update', ['product' => $product->id], false), 
            $productData
        );
        
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors($expectedErrors);
    }

    public function test_it_should_update_a_product()
    {   
        $product = Product::factory()->create();

        $response = $this->putJson(
            route('api.products.update', ['product' => $product->id], false), 
            [
                ...$product->toArray(),
                'name' => $product->name . 'EDITED',
            ]
        );

        $response->assertOk();

        $this->assertDatabaseHas('products', [
            'name' => $product->name . 'EDITED',
        ]);
    }

    public function test_it_should_not_update_a_product_with_duplicated_name()
    {   
        $firstProduct = Product::factory()->create();
        $otherProduct = Product::factory()->create();

        $response = $this->putJson(
            route('api.products.update', ['product' => $firstProduct->id], false), 
            [
                ...$firstProduct->toArray(),
                'name' => $otherProduct->name,
            ]
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name']);
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
