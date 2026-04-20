<?php

namespace App\Providers;

use App\Models\RequestLayout;
use App\Models\RequestRecord;
use App\Services\DadataNameDeclensionService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DadataNameDeclensionService::class, function (): DadataNameDeclensionService {
            return new DadataNameDeclensionService(
                (bool) config('dadata.enabled'),
                (string) config('dadata.token'),
                (string) config('dadata.secret'),
                (string) config('dadata.name_case'),
                (int) config('dadata.timeout'),
                (int) config('dadata.cache_ttl'),
            );
        });
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
