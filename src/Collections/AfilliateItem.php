<?php

namespace Larsvg\StatamicAffiliate\Collections;

use Statamic\Taxonomies\LocalizedTerm;

class AfilliateItem
{
    public function __construct(
        protected string $productId,
        protected string $merchantId,
        protected ?string $merchantName,
        protected ?LocalizedTerm $mechantTaxonomy,
        protected string $afilliateLink,
        protected string $productName,
        protected string $productDescription,
        protected float $price,
        protected float $deliveryCost,
        protected string $image,
        protected int $stock,
    ) {

    }
}
