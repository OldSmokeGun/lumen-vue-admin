<?php

namespace App\Providers;

use App\Utils\HttpResponse\HttpResponse;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Http\ResponseFactory;

class HttpResponseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(HttpResponse::class, function () {
            return new HttpResponse($this->app->make(ResponseFactory::class));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
