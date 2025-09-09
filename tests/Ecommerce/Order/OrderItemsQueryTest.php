<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Tests\Ecommerce\Order;

use PDO;
use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\Order\OrderItemsQuery;
use RefactoringChallenge\Tech\DependencyInjection\ContainerFactory;
use RefactoringChallenge\Tests\TestingDatabase;

class OrderItemsQueryTest extends TestCase
{
    private OrderItemsQuery $orderItemsQuery;
    private PDO $pdo;

    protected function setUp(): void
    {
        $container = ContainerFactory::get();
        $this->orderItemsQuery = $container->get(OrderItemsQuery::class);
        $this->pdo = $container->get(PDO::class);

        TestingDatabase::prepareFreshData();
    }

    public function testAddOrderItemInsertsCorrectly(): void
    {
        $orderId = 99;
        $productId = 99;
        $quantity = 2;
        $unitPrice = 123.45;
        $totalPrice = 246.90;
        $productName = 'Test Product Name';
        $productSku = 'TEST-SKU-99';

        $this->orderItemsQuery->addOrderItem(
            $orderId,
            $productId,
            $quantity,
            $unitPrice,
            $totalPrice,
            $productName,
            $productSku
        );

        // Verify the order item was inserted
        $stmt = $this->pdo->prepare("SELECT * FROM order_items WHERE order_id = ? AND product_id = ?");
        $stmt->execute([$orderId, $productId]);

        /**
         * @var false|array{
         *     order_id: int,
         *     product_id: int,
         *     quantity: int,
         *     unit_price: numeric-string,
         *     total_price: numeric-string,
         *     product_name: string,
         *     product_sku: string,
         * } $orderItem
         */
        $orderItem = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($orderItem);
        $this->assertEquals($orderId, $orderItem['order_id']);
        $this->assertEquals($productId, $orderItem['product_id']);
        $this->assertEquals($quantity, $orderItem['quantity']);
        $this->assertEquals($unitPrice, (float) $orderItem['unit_price']);
        $this->assertEquals($totalPrice, (float) $orderItem['total_price']);
        $this->assertEquals($productName, $orderItem['product_name']);
        $this->assertEquals($productSku, $orderItem['product_sku']);
    }
}