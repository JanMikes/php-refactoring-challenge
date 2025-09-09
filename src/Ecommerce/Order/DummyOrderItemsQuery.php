<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Order;

final class DummyOrderItemsQuery implements OrderItemsQuery
{
    /**
     * @var array<int, array<array{
     *     order_id: int,
     *     product_id: int,
     *     quantity: int,
     *     unit_price: float,
     *     total_price: float,
     *     product_name: string,
     *     product_sku: string
     * }>>
     */
    private array $orderItems = [];

    public function addOrderItem(
        int $orderId,
        int $productId,
        int $quantity,
        float $unitPrice,
        float $totalPrice,
        string $productName,
        string $productSku,
    ): void {
        if (!isset($this->orderItems[$orderId])) {
            $this->orderItems[$orderId] = [];
        }

        $this->orderItems[$orderId][] = [
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'product_name' => $productName,
            'product_sku' => $productSku,
        ];
    }

    /**
     * @return array<array{
     *     order_id: int,
     *     product_id: int,
     *     quantity: int,
     *     unit_price: float,
     *     total_price: float,
     *     product_name: string,
     *     product_sku: string
     * }>
     */
    public function getOrderItems(int $orderId): array
    {
        return $this->orderItems[$orderId] ?? [];
    }
}