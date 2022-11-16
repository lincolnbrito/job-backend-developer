<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_should_return_empty_list()
    {
        $response = $this->get(route('api.products.index', [], false));

        $response->assertExactJson([]);
    }

    public function test_it_should_filter_products_by_name_and_category()
    {
        $product = Product::factory()->create();

        $response = $this->get(route('api.products.index', ['search' => $product->name], false));

        $response->assertJson([$product->toArray()]);
    }

    public function test_it_should_filter_products_by_category(

    )
    {
        $products = Product::factory()
            ->count(10)
            ->state(new Sequence(
                ['category' => $this->faker()->word],
                ['category' => $this->faker()->word]
            ))
            ->create();

        $categoryToFind = $products->first()->category;

        $response = $this->get(route('api.products.index', ['category' => $categoryToFind], false));
        
        $response
            ->assertJsonCount(5)
            ->assertJsonFragment(['category' => $categoryToFind]);
    }

    /**
     * @dataProvider provideFilterByImages
     */
    public function test_it_should_filter_products_by_image(
        ?bool $hasImage,
        int $countWithImage,
        int $countWithoutImage,
        int $expectFiltered,
        int $expectTotalOnDatabase
     )
    {
        Product::factory()
            ->count($countWithImage)
            ->create();
        
        Product::factory()
            ->count($countWithoutImage)
            ->withoutImage()
            ->create();

        $response = $this->get(route('api.products.index', ['has_image' => $hasImage], false));
        
        $response->assertJsonCount($expectFiltered);
        
        $this->assertEquals($expectTotalOnDatabase, $countWithImage + $countWithoutImage);
        $this->assertDatabaseCount('products', $expectTotalOnDatabase);
    }

    public function provideProductsCategory()
    {
        return [
            ['category #1', 15, 5, 15, 20],
            ['category #2', 0, 41, 0, 41],
            ['category #3', 20, 40, 20, 60]
        ];
    } 

    public function provideFilterByImages()
    {
        return [
            'filter with image #1' => [
                true, 10, 2, 10, 12
            ],
            'filter with image #2' => [
                true, 2, 2, 2, 4
            ],
            'filter without image #1' => [
                false, 10, 2, 2, 12
            ],
            'filter without image #2' => [
                false, 22, 31, 31, 53
            ],
            'return all #1' => [
                null, 10, 2, 12, 12
            ]
        ];
    }

}
