<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Order;

use PDO;

readonly final class OrderLogsQuery
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    public function logOrderCreated(int $orderId): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO order_logs (order_id, action, new_status, description) VALUES (?, 'created', 'pending', 'Order created')");
        $stmt->execute([$orderId]);
    }

    public function logStatusChange(int $orderId, OrderStatus $oldStatus, OrderStatus $newStatus): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO order_logs (order_id, action, old_status, new_status, description) VALUES (?, 'status_change', ?, ?, 'Status updated')");
        $stmt->execute([$orderId, $oldStatus->value, $newStatus->value]);
    }
}