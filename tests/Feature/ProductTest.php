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

    /**
     * @dataProvider provideSearch
     */
    public function test_it_should_filter_products_by_name_and_category(
        $product,
        $category,
        $searchTerm,
        $expectFiltered,
        $expectTotalOnDatabase
    )
    {
        Product::factory()
            ->count($product['count'])
            ->sequence(fn ($sequence) => ['name' => $product['name'].$sequence->index])
            ->create([
               'category' => $category['name']
            ]);

        Product::factory()
            ->count($category['count'])
            ->create([
                'category' => $category['name']
            ]);

        $response = $this->get(route('api.products.index', ['search' => $searchTerm], false));
        $response->assertJsonCount($expectFiltered);

        $this->assertDatabaseCount('products', $expectTotalOnDatabase);
    }

    /**
     * @dataProvider provideFilterByCategory
     */
    public function test_it_should_filter_products_by_category(
        $categoryToFind,
        $countCategory,
        $countOtherCategories,
        $expectFiltered,
        $expectTotalOnDatabase
    )
    {
        $products = Product::factory()
            ->count($countCategory)
            ->create(['category' => $categoryToFind]);
        
        $productsOtherCategory = Product::factory()
            ->count($countOtherCategories)
            ->create();

        $response = $this->get(route('api.products.index', ['category' => $categoryToFind], false));
        
        $response->assertJsonCount($expectFiltered);
        $this->assertEquals($expectTotalOnDatabase, $products->count() + $productsOtherCategory->count());
        $this->assertDatabaseCount('products', $expectTotalOnDatabase);
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

    public function provideSearch()
    {
        return [
            'search on product name' => [
                'product' => ['name' => 'fake product', 'count' => 10], 
                'category' => ['name' => 'fake category', 'count' => 20],
                'search' => 'product',
                'expectedFiltered' => 10,
                'expectInDatabase' => 30
            ],
            'search on product name or category' => [
                'product' => ['name' => 'fake product', 'count' => 10], 
                'category' => ['name' => 'fake category', 'count' => 20],
                'search' => 'fake',
                'expectedFiltered' => 30,
                'expectInDatabase' => 30
            ],
            'search not in product name or category' => [
                'product' => ['name' => 'fake product', 'count' => 10], 
                'category' => ['name' => 'fake category', 'count' => 20],
                'search' => 'another term',
                'expectedFiltered' => 0,
                'expectInDatabase' => 30
            ]
        ];
    }

    public function provideFilterByCategory()
    {
        return [
            ['category #1', 15, 5, 15, 20],
            ['category #2', 1, 41, 1, 42],
            ['category #3', 20, 40, 20, 60],
            [null, 0, 40, 40, 40]
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
            'no filter' => [
                null, 10, 2, 12, 12
            ]
        ];
    }

}
