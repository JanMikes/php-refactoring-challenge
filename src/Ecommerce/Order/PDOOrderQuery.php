<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Order;

use PDO;

readonly final class PDOOrderQuery implements OrderQuery
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    /**
     * @throws OrderNotFound
     */
    public function getOrderStatus(int $orderId): OrderStatus
    {
        $stmt = $this->pdo->prepare("SELECT status FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);

        /**
         * @var false|array{
         *     status: string,
         * } $data
         */
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data === false) {
            throw new OrderNotFound($orderId);
        }

        return OrderStatus::from($data['status']);
    }

    /**
     * @throws OrderNotFound
     */
    public function changeOrderStatus(int $orderId, OrderStatus $newStatus): void
    {
        // First check if order exists by getting current status
        $this->getOrderStatus($orderId);

        $stmt = $this->pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus->value, $orderId]);
    }

    /**
     * @throws OrderCreationFailed
     */
    public function createOrder(int $customerId, string $orderNumber, float $totalAmount, string $shippingAddress): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO orders (customer_id, order_number, total_amount, shipping_address, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$customerId, $orderNumber, $totalAmount, $shippingAddress, OrderStatus::Pending->value]);
        
        $lastInsertedId = $this->pdo->lastInsertId();
        
        if ($lastInsertedId === false) {
            throw new OrderCreationFailed();
        }
        
        return (int) $lastInsertedId;
    }
}