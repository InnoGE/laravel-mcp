<?php

namespace InnoGE\LaravelMcp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \InnoGE\LaravelMcp\LaravelMcp
 */
class LaravelMcp extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \InnoGE\LaravelMcp\LaravelMcp::class;
    }
}
