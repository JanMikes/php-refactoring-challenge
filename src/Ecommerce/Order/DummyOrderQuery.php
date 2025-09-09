<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Order;

final class DummyOrderQuery implements OrderQuery
{
    /**
     * @var array<int, array{
     *     id: int,
     *     customer_id: int,
     *     order_number: string,
     *     total_amount: float,
     *     shipping_address: string,
     *     status: OrderStatus
     * }>
     */
    private array $orders = [];

    private int $nextOrderId = 1;

    public function getOrderStatus(int $orderId): OrderStatus
    {
        if (!isset($this->orders[$orderId])) {
            throw new OrderNotFound($orderId);
        }

        return $this->orders[$orderId]['status'];
    }

    public function changeOrderStatus(int $orderId, OrderStatus $newStatus): void
    {
        if (!isset($this->orders[$orderId])) {
            throw new OrderNotFound($orderId);
        }

        $this->orders[$orderId]['status'] = $newStatus;
    }

    public function createOrder(int $customerId, string $orderNumber, float $totalAmount, string $shippingAddress): int
    {
        $orderId = $this->nextOrderId++;

        $this->orders[$orderId] = [
            'id' => $orderId,
            'customer_id' => $customerId,
            'order_number' => $orderNumber,
            'total_amount' => $totalAmount,
            'shipping_address' => $shippingAddress,
            'status' => OrderStatus::Pending,
        ];

        return $orderId;
    }

    public function addOrder(int $orderId, OrderStatus $status): void
    {
        $this->orders[$orderId] = [
            'id' => $orderId,
            'customer_id' => 1,
            'order_number' => 'TEST-' . $orderId,
            'total_amount' => 100.0,
            'shipping_address' => 'Test Address',
            'status' => $status,
        ];
    }
}