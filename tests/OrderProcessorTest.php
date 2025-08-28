<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use RefactoringChallenge\OrderProcessor;

class OrderProcessorTest extends TestCase
{

    private $db;

    private $orderProcessor;

    protected function setUp(): void
    {
        $this->db = new PDO(
            'mysql:host=' . $_ENV['MYSQL_HOST'] . ';dbname=' . $_ENV['MYSQL_DATABASE'],
            $_ENV['MYSQL_USER'],
            $_ENV['MYSQL_PASSWORD']
        );
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->db->exec("DELETE FROM order_logs");
        $this->db->exec("DELETE FROM order_items");
        $this->db->exec("DELETE FROM orders");
        $this->db->exec("UPDATE inventory SET quantity_available = 10, quantity_reserved = 0 WHERE product_id = 1");
        $this->db->exec("DELETE FROM customers WHERE id = 99");
        $this->db->exec("DELETE FROM products WHERE id = 99");
        $this->db->exec("DELETE FROM inventory WHERE product_id = 99");

        $this->db->exec("INSERT IGNORE INTO customers (id, email, first_name) VALUES (99, 'test@example.com', 'Tester')");
        $this->db->exec("INSERT IGNORE INTO products (id, name, price, sku) VALUES (99, 'Test Produkt', 123.45, 'TEST-99')");
        $this->db->exec("INSERT IGNORE INTO inventory (product_id, quantity_available, quantity_reserved) VALUES (99, 10, 0)");

        $this->orderProcessor = new OrderProcessor();
    }

    public function testProcessOrderSuccess()
    {
        $items = [['product_id' => 99, 'quantity' => 2],];
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