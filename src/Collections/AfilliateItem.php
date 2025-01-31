<?php

namespace Larsvg\StatamicAffiliate\Collections;

use Statamic\Taxonomies\LocalizedTerm;

class AfilliateItem
{
    public function __construct(
        public string $productId,
        public string $merchantId,
        public ?string $merchantName,
        public ?LocalizedTerm $mechantTaxonomy,
        public string $afilliateLink,
        public string $productName,
        public string $productDescription,
        public float $price,
        public float $deliveryCost,
        public string $image,
        public int $stock,
    ) {

    }
}
