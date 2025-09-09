<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Order;

readonly final class OrderStatusChanged
{
    public function __construct(
        public int $orderId,
        public OrderStatus $oldStatus,
        public OrderStatus $newStatus,
    ) {
    }
}