<?php

namespace PlinCode\IstatForeignCountries\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PlinCode\IstatForeignCountries\IstatForeignCountries
 */
class IstatForeignCountries extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \PlinCode\IstatForeignCountries\IstatForeignCountries::class;
    }
}
