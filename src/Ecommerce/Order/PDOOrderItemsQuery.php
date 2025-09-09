<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Order;

use PDO;

readonly final class PDOOrderItemsQuery implements OrderItemsQuery
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    public function addOrderItem(
        int $orderId,
        int $productId,
        int $quantity,
        float $unitPrice,
        float $totalPrice,
        string $productName,
        string $productSku,
    ): void {
        $stmt = $this->pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price, product_name, product_sku) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $orderId,
            $productId,
            $quantity,
            $unitPrice,
            $totalPrice,
            $productName,
            $productSku,
        ]);
    }
}