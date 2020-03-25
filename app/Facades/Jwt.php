<?php


namespace App\Facades;


use Illuminate\Support\Facades\Facade;

class Jwt extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Utils\Jwt\Jwt::class;
    }
}
