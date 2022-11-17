<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ExternalStoreApi\ExternalStoreApiService;
use Illuminate\Console\Command;

class ProductImportCommand extends Command
{
    protected $signature = 'products:import {--id=}';

    protected $description = 'Import products from Fake Store API';

    protected $apiService;

    public function __construct()
    {
        parent::__construct();

        $this->apiService = new ExternalStoreApiService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if($this->option('id')) {
            $this->handleSingleProduct();
            return;
        }

        $this->handleProductList();
    }

    protected function handleSingleProduct()
    {
        $productData = $this->apiService->fetchProduct($this->option('id'));

        if(!$productData) {
            $this->warn('Product not found');
            return;
        }

        $this->info('Importing product');

        $product = Product::updateOrCreate([
            'id' => $productData->id
        ], [
            ...get_object_vars($productData),
            'name' => $productData->title
        ]);

        $this->info("Product: #{$product->id} '{$product->name}' imported with success");
    }

    protected function handleProductList()
    {
        $products = $this->apiService->fetchProducts();

        if(empty($products)){
            $this->warn('No products to import');
            return;
        }

        $this->info('Importing products...');
        $this->newLine();

        $this->output->progressStart(count($products));
        
        foreach($products as $product) {
            $this->output->progressAdvance();
            
            Product::updateOrCreate([
                'id' => $product->id
            ], [
                ...get_object_vars($product),
                'name' => $product->title
            ]);
        };
        
        $this->output->progressFinish();
        $this->info('Products imported successfully');
    }
}
