<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Tests\Ecommerce;

use PDO;
use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\Cart\CartItem;
use RefactoringChallenge\Ecommerce\OrderProcessor;
use RefactoringChallenge\Tech\DependencyInjection\ContainerFactory;
use RefactoringChallenge\Tests\TestingDatabase;

class OrderProcessorTest extends TestCase
{
    private PDO $db;
    private OrderProcessor $orderProcessor;

    protected function setUp(): void
    {
        $container = ContainerFactory::get();
        $this->db = $container->get(PDO::class);
        $this->orderProcessor = $container->get(OrderProcessor::class);

        TestingDatabase::prepareFreshData();
    }

    public function testProcessOrderSuccess()
    {
        $items = [new CartItem(productId: 99, quantity: 2)];
        $shippingAddress = "Testovací 123, Testov";

        $orderId = $this->orderProcessor->processOrder(99, $items, $shippingAddress);

        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($order);
        $this->assertEquals(99, $order['customer_id']);
        $this->assertEquals(123.45 * 2, $order['total_amount']);
        $this->assertEquals($shippingAddress, $order['shipping_address']);

        $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $orderItem = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(2, $orderItem['quantity']);
        $this->assertEquals(123.45, $orderItem['unit_price']);
        $this->assertEquals('Test Produkt', $orderItem['product_name']);
    }
}