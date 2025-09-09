<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Warehouse;

readonly final class Product
{
    public function __construct(
        public int $id,
        public string $name,
        public float $price,
        public string $sku,
    ) {
    }
}