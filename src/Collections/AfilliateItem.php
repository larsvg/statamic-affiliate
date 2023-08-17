<?php

namespace Larsvg\StatamicAffiliate\Collections;

class AfilliateItem
{
    public function __construct(
        private string $productName,
        private string $productDescription,
        private float $deliveryCost,
        private string $image,
        private int $stock
    ) {

    }
}
