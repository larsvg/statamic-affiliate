<?php

namespace Larsvg\StatamicAffiliate\Collections;

class AfilliateItem
{
    public function __construct(
        readonly private string $productId,
        readonly private string $merchantId,
        readonly private string $productName,
        readonly private string $productDescription,
        readonly private float $price,
        readonly private float $deliveryCost,
        readonly private string $image,
        readonly private int $stock
    ) {

    }

}
