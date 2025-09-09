<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Tests;

use PDO;
use RefactoringChallenge\Tech\DependencyInjection\ContainerFactory;

class TestingDatabase
{
    public static function prepareFreshData(): void
    {
        $container = ContainerFactory::get();
        $db = $container->get(PDO::class);

        $db->exec("DELETE FROM order_logs");
        $db->exec("DELETE FROM order_items");
        $db->exec("DELETE FROM orders");
        $db->exec("UPDATE inventory SET quantity_available = 10, quantity_reserved = 0 WHERE product_id = 1");
        $db->exec("DELETE FROM customers WHERE id = 99");
        $db->exec("DELETE FROM products WHERE id = 99");
        $db->exec("DELETE FROM inventory WHERE product_id = 99");

        $db->exec("INSERT IGNORE INTO customers (id, email, first_name) VALUES (99, 'test@example.com', 'Tester')");
        $db->exec("INSERT IGNORE INTO products (id, name, price, sku) VALUES (99, 'Test Produkt', 123.45, 'TEST-99')");
        $db->exec("INSERT IGNORE INTO inventory (product_id, quantity_available, quantity_reserved) VALUES (99, 10, 0)");
        $db->exec("INSERT IGNORE INTO orders (id, customer_id, order_number, total_amount, shipping_address, status) VALUES (99, 99, 'TEST-ORDER-99', 123.45, 'Test Address', 'pending')");
    }
}