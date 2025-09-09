<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Order;

interface OrderQuery
{
    /**
     * @throws OrderNotFound
     */
    public function getOrderStatus(int $orderId): OrderStatus;

    /**
     * @throws OrderNotFound
     */
    public function changeOrderStatus(int $orderId, OrderStatus $newStatus): void;

    /**
     * @throws OrderCreationFailed
     */
    public function createOrder(int $customerId, string $orderNumber, float $totalAmount, string $shippingAddress): int;
}