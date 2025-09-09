<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Cart;

readonly final class CartItem
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {
    }
}