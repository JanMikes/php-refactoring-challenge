<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Ecommerce;

use PDO;
use Psr\Log\LoggerInterface;
use RefactoringChallenge\Ecommerce\Cart\CartItem;
use RefactoringChallenge\Ecommerce\Customer\CustomerNotFound;
use RefactoringChallenge\Ecommerce\Customer\CustomerQuery;
use RefactoringChallenge\Ecommerce\Warehouse\InsufficientStock;
use RefactoringChallenge\Ecommerce\Warehouse\InventoryQuery;
use RefactoringChallenge\Ecommerce\Warehouse\ProductNotFound;
use RefactoringChallenge\Ecommerce\Warehouse\ProductQuery;

readonly class OrderProcessor
{
    public function __construct(
        private PDO $db,
        private LoggerInterface $logger,
        private ProductQuery $productQuery,
        private InventoryQuery $inventoryQuery,
        private CustomerQuery $customerQuery,
        private OrderNumberGenerator $orderNumberGenerator,
    ) {
    }

    /**
     * @param list<CartItem> $items
     *
     * @throws ProductNotFound
     * @throws InsufficientStock
     * @throws CustomerNotFound
     * @throws OrderCreationFailed
     */
    public function processOrder(int $customerId, array $items, string $shippingAddress): int
    {
        $orderNumber = $this->orderNumberGenerator->next();
        $totalAmount = 0;

        foreach ($items as $item) {
            $price = $this->productQuery->getPrice($item->productId);
            $availableStock = $this->inventoryQuery->getStock($item->productId);

            if ($availableStock < $item->quantity) {
                throw new InsufficientStock(
                    productId: $item->productId,
                    requestedQuantity: $item->quantity,
                    stockAvailable: $availableStock,
                );
            }

            $totalAmount += MoneyCalculator::multiply($price, $item->quantity);
        }

        $stmt = $this->db->prepare("INSERT INTO orders (customer_id, order_number, total_amount, shipping_address, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$customerId, $orderNumber, $totalAmount, $shippingAddress, OrderStatus::Pending->value]);
        $lastInsertedId = $this->db->lastInsertId();

        if ($lastInsertedId === false) {
            throw new OrderCreationFailed();
        }

        $orderId = (int) $lastInsertedId;

        foreach ($items as $item) {
            $product = $this->productQuery->getById($item->productId);

            $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price, product_name, product_sku) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $orderId,
                $item->productId,
                $item->quantity,
                $product->price,
                MoneyCalculator::multiply($product->price, $item->quantity),
                $product->name,
                $product->sku
            ]);

            $stmt = $this->db->prepare("UPDATE inventory SET quantity_available = quantity_available - ?, quantity_reserved = quantity_reserved + ? WHERE product_id = ?");
            $stmt->execute([$item->quantity, $item->quantity, $item->productId]);
        }

        $stmt = $this->db->prepare("INSERT INTO order_logs (order_id, action, new_status, description) VALUES (?, 'created', 'pending', 'Order created')");
        $stmt->execute([$orderId]);

        $this->sendOrderConfirmationEmail($customerId, $orderId);

        return $orderId;
    }

    /**
     * @throws CustomerNotFound
     */
    private function sendOrderConfirmationEmail(int $customerId, int $orderId): void
    {
        $customer = $this->customerQuery->getById($customerId);

        $this->logger->info('Sending email', [
            'email' => $customer->email,
            'orderId' => $orderId,
        ]);
    }

    public function updateOrderStatus(int $orderId, OrderStatus $newStatus): void
    {
        $stmt = $this->db->prepare("SELECT status FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new \Exception("Order not found");
        }

        $oldStatus = OrderStatus::from($order['status']);

        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus->value, $orderId]);

        $stmt = $this->db->prepare("INSERT INTO order_logs (order_id, action, old_status, new_status, description) VALUES (?, 'status_change', ?, ?, 'Status updated')");
        $stmt->execute([$orderId, $oldStatus, $newStatus]);

        if ($newStatus === OrderStatus::Shipped) {
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
