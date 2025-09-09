<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Tests\UseCase;

use PDO;
use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\Cart\CartItem;
use RefactoringChallenge\Tech\DependencyInjection\ContainerFactory;
use RefactoringChallenge\Tests\TestingDatabase;
use RefactoringChallenge\UseCase\PutOrderUseCase;

class PutOrderUseCaseTest extends TestCase
{
    private PDO $db;
    private PutOrderUseCase $orderProcessor;

    protected function setUp(): void
    {
        $container = ContainerFactory::get();
        $this->db = $container->get(PDO::class);
        $this->orderProcessor = $container->get(PutOrderUseCase::class);

        TestingDatabase::prepareFreshData();
    }

    public function testProcessOrderSuccess(): void
    {
        $items = [new CartItem(productId: 99, quantity: 2)];
        $shippingAddress = "Testovací 123, Testov";

        $orderId = $this->orderProcessor->handle(99, $items, $shippingAddress);

        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);

        /**
         * @var false|array{
         *     customer_id: int,
         *     total_amount: numeric-string,
         *     shipping_address: string,
         * } $order
         */
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($order);
        $this->assertEquals(99, $order['customer_id']);
        $this->assertEquals(123.45 * 2, $order['total_amount']);
        $this->assertEquals($shippingAddress, $order['shipping_address']);

        $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);

        /**
         * @var array{
         *     quantity: int,
         *     unit_price: numeric-string,
         *     product_name: string,
         * } $orderItem
         */
        $orderItem = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(2, $orderItem['quantity']);
        $this->assertEquals(123.45, $orderItem['unit_price']);
        $this->assertEquals('Test Produkt', $orderItem['product_name']);
    }
}