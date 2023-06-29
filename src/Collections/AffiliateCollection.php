<?php

namespace Larsvg\StatamicAffiliate\Collections;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Larsvg\StatamicAffiliate\Exceptions\AffiliateException;

class AffiliateCollection extends Collection
{

    public function offsetSet($key, $value): void
    {
        if (!$value instanceof AfilliateItem) {
            throw new \InvalidArgumentException('Invalid item type.');
        }

        parent::offsetSet($key, $value);
    }


    public function add($item): AffiliateCollection
    {
        if (!$item instanceof AfilliateItem) {
            throw new \InvalidArgumentException('Invalid item type.');
        }

        return parent::add($item);
    }

}
