<?php

namespace Larsvg\StatamicAffiliate\Collections;

use Illuminate\Support\Collection;

class AffiliateCollection extends Collection
{
    public function offsetSet($key, $value): void
    {
        if (! $value instanceof AfilliateItem) {
            throw new \InvalidArgumentException('Invalid item type.');
        }

        parent::offsetSet($key, $value);
    }

    public function add($item): AffiliateCollection
    {
        if (! $item instanceof AfilliateItem) {
            throw new \InvalidArgumentException('Invalid item type.');
        }

        return parent::add($item);
    }
}
