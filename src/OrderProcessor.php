<?php

declare(strict_types = 1);

namespace RefactoringChallenge;

use PDO;
use Psr\Log\LoggerInterface;

readonly class OrderProcessor
{
    public function __construct(
        private PDO $db,
        private LoggerInterface $logger,
    ) {
    }

    public function processOrder($customerId, $items, $shippingAddress)
    {
        $orderNumber = 'ORD-' . date('Y') . '-' . rand(1000, 9999);
        $totalAmount = 0;

        foreach ($items as $item) {
            $stmt = $this->db->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->execute([$item['product_id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new \Exception("Product not found");
            }

            $totalAmount += $product['price'] * $item['quantity'];

            $stmt = $this->db->prepare("SELECT quantity_available FROM inventory WHERE product_id = ?");
            $stmt->execute([$item['product_id']]);
            $inventory = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($inventory['quantity_available'] < $item['quantity']) {
                throw new \Exception("Not enough stock");
            }
        }

        $stmt = $this->db->prepare("INSERT INTO orders (customer_id, order_number, total_amount, shipping_address, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$customerId, $orderNumber, $totalAmount, $shippingAddress]);
        $orderId = $this->db->lastInsertId();

        foreach ($items as $item) {
            $stmt = $this->db->prepare("SELECT name, price, sku FROM products WHERE id = ?");
            $stmt->execute([$item['product_id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price, product_name, product_sku) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $product['price'],
                $product['price'] * $item['quantity'],
                $product['name'],
                $product['sku']
            ]);

            $stmt = $this->db->prepare("UPDATE inventory SET quantity_available = quantity_available - ?, quantity_reserved = quantity_reserved + ? WHERE product_id = ?");
            $stmt->execute([$item['quantity'], $item['quantity'], $item['product_id']]);
        }

        $stmt = $this->db->prepare("INSERT INTO order_logs (order_id, action, new_status, description) VALUES (?, 'created', 'pending', 'Order created')");
        $stmt->execute([$orderId]);

        $this->sendOrderConfirmationEmail($customerId, $orderId);

        return $orderId;
    }

    private function sendOrderConfirmationEmail($customerId, $orderId): void
    {
        $stmt = $this->db->prepare("SELECT email, first_name FROM customers WHERE id = ?");
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->logger->info('Sending email', [
            'email' => $customer['email'],
            'orderId' => $orderId,
        ]);
    }

    public function updateOrderStatus($orderId, $newStatus)
    {
        $stmt = $this->db->prepare("SELECT status FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new \Exception("Order not found");
        }

        $oldStatus = $order['status'];

        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);

        $stmt = $this->db->prepare("INSERT INTO order_logs (order_id, action, old_status, new_status, description) VALUES (?, 'status_change', ?, ?, 'Status updated')");
        $stmt->execute([$orderId, $oldStatus, $newStatus]);

        if ($newStatus === 'shipped') {
            $this->sendShippingNotification($orderId);
        }
    }

    private function sendShippingNotification($orderId): void
    {
        // In real system, there could be event and handler for it

        $this->logger->info("Sending shipping notification", [
            'order_id' => $orderId,
        ]);
    }
}
