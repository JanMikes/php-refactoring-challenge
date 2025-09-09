<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Ecommerce;

use PDO;
use Psr\Log\LoggerInterface;

readonly class OrderProcessor
{
    public function __construct(
        private PDO $db,
        private LoggerInterface $logger,
        private ProductQuery $orderQuery,
        private InventoryQuery $inventoryQuery,
    ) {
    }

    /**
     * @param list<CartItem> $items
     *
     * @throws ProductNotFound
     * @throws InsufficientStock
     */
    public function processOrder($customerId, array $items, $shippingAddress)
    {
        $orderNumber = 'ORD-' . date('Y') . '-' . rand(1000, 9999);
        $totalAmount = 0;

        foreach ($items as $item) {
            $price = $this->orderQuery->getPrice($item->productId);
            $requestedQuantity = $item->quantity;
            $availableStock = $this->inventoryQuery->getStock($item->productId);

            if ($availableStock < $item->quantity) {
                throw new InsufficientStock(
                    productId: $item->productId,
                    requestedQuantity: $requestedQuantity,
                    stockAvailable: $availableStock,
                );
            }

            $totalAmount += $price * $requestedQuantity;
        }

        $stmt = $this->db->prepare("INSERT INTO orders (customer_id, order_number, total_amount, shipping_address, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$customerId, $orderNumber, $totalAmount, $shippingAddress]);
        $orderId = $this->db->lastInsertId();

        foreach ($items as $item) {
            $stmt = $this->db->prepare("SELECT name, price, sku FROM products WHERE id = ?");
            $stmt->execute([$item->productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price, product_name, product_sku) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $orderId,
                $item->productId,
                $item->quantity,
                $product['price'],
                $product['price'] * $item->quantity,
                $product['name'],
                $product['sku']
            ]);

            $stmt = $this->db->prepare("UPDATE inventory SET quantity_available = quantity_available - ?, quantity_reserved = quantity_reserved + ? WHERE product_id = ?");
            $stmt->execute([$item->quantity, $item->quantity, $item->productId]);
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
