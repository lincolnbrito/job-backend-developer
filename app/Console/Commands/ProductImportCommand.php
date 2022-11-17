<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ExternalStoreApi\ExternalStoreApiService;
use App\Services\ExternalStoreApi\ExternalStoreApiServiceInterface;
use Exception;
use Illuminate\Console\Command;

class ProductImportCommand extends Command
{
    protected $signature = 'products:import {--id=}';

    protected $description = 'Import products from Fake Store API';

    protected $apiService;

    public function __construct()
    {
        parent::__construct();

        $this->apiService = app(ExternalStoreApiServiceInterface::class);
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
        try {
            $productData = $this->apiService->fetchProduct($this->option('id'));

            $this->info('Importing product');

            $product = Product::updateOrCreate([
                'id' => $productData->id
            ], [
                ...get_object_vars($productData),
                'name' => $productData->title
            ]);

            $this->info("Product: #{$product->id} '{$product->name}' imported with success");
           
        } catch( Exception $e) {
            $this->warn($e->getMessage());
            return Command::FAILURE;
        }
        
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
