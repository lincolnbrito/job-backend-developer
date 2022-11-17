<?php

namespace App\Providers;

use App\Services\ExternalStoreApi\ExternalStoreApiServiceInterface;
use App\Services\ExternalStoreApi\FakeStoreApiService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(ExternalStoreApiServiceInterface::class, FakeStoreApiService::class);
    }
}
