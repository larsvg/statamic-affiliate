<?php

namespace Larsvg\StatamicAffiliate\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Larsvg\StatamicAffiliate\StatamicAffiliate
 */
class StatamicAffiliate extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Larsvg\StatamicAffiliate\StatamicAffiliate::class;
    }
}
