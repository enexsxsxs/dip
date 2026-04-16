<?php

namespace App\Providers;

use App\Models\RequestLayout;
use App\Models\RequestRecord;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('layout', function (string $value) {
            return RequestLayout::withTrashed()->whereKey($value)->firstOrFail();
        });

        Route::bind('record', function (string $value) {
            return RequestRecord::withTrashed()->whereKey($value)->firstOrFail();
        });
    }
}
