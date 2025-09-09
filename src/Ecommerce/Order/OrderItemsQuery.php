<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Order;

interface OrderItemsQuery
{
    public function addOrderItem(
        int $orderId,
        int $productId,
        int $quantity,
        float $unitPrice,
        float $totalPrice,
        string $productName,
        string $productSku,
    ): void;
}