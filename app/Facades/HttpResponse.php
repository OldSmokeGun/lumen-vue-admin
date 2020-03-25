<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class HttpResponse extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Utils\HttpResponse\HttpResponse::class;
    }
}
