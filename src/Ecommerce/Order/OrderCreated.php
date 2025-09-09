<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Order;

readonly final class OrderCreated
{
    public function __construct(
        public int $orderId,
        public int $customerId,
    ) {
    }
}