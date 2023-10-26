<?php

namespace Larsvg\StatamicAffiliate\Collections;

class AfilliateItem
{
    public function __construct(
        readonly public string $productId,
        readonly public string $merchantId,
        readonly public string $productName,
        readonly public string $productDescription,
        readonly public float $price,
        readonly public float $deliveryCost,
        readonly public string $image,
        readonly public int $stock
    ) {

    }

}
