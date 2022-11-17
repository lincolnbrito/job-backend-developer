<?php

namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductImportCommandTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $baseUrl;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->baseUrl =  'http://anotherfakestore.com';

        config(['services.external_store.endpoint' => $this->baseUrl]);

    }

    public function test_it_should_not_import_invalid_product()
    {
        Http::fake([
            "{$this->baseUrl}/products/*" => Http::response(null, 200),
        ]);

        $this->artisan("products:import", ["--id" => 1000])
            ->expectsOutput('Product not found');

        $this->assertDatabaseCount('products', 0);
    }

    /** 
     * @dataProvider provideProducts
     */
    public function test_it_should_import_all_products(
        $products,
        $expectedOutput,
        $expectedDatabaseCount
    )
    {
        Http::fake([
            "{$this->baseUrl}/products" => Http::response($products, 200),
        ]);

        $this->artisan("products:import", [])
            ->expectsOutput($expectedOutput)
            ->assertSuccessful();

        $this->assertDatabaseCount('products', $expectedDatabaseCount);
    }

    public function provideProducts()
    {
        return [
            [
                'products' => [],
                'expectedOutput' => 'No products to import',
                'expectedDatabase' => 0
            ],
            [
                'products' => [
                    [
                        'id' => 1, 
                        'title' => 'fake 1', 
                        'description' => 'asdasd', 
                        'category' => 'category 1', 
                        'price' => 10,
                        'image' => 'http://images.com/1'
                    ]
                ],
                'expectedOutput' => 'Products imported successfully',
                'expectedDatabase' => 1
            ],
            [
                'products' => [
                    [
                        'id' => 1, 
                        'title' => 'fake 1', 
                        'description' => 'asdasd', 
                        'category' => 'category 1', 
                        'price' => 10.45,
                        'image' => 'http://images.com/1'
                    ],
                    [
                        'id' => 2, 
                        'title' => 'fake 2', 
                        'description' => 'asdasd', 
                        'category' => 'category 2', 
                        'price' => 3.34,
                        'image' => 'http://images.com/1'
                    ]
                ],
                'expectedOutput' => 'Products imported successfully',
                'expectedDatabase' => 2
            ]
        ];
    }


}
