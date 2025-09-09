<?php

declare(strict_types = 1);

namespace RefactoringChallenge\UseCase;

use Psr\Log\LoggerInterface;
use RefactoringChallenge\Ecommerce\Cart\CartItem;
use RefactoringChallenge\Ecommerce\Customer\CustomerNotFound;
use RefactoringChallenge\Ecommerce\Customer\CustomerQuery;
use RefactoringChallenge\Ecommerce\MoneyCalculator;
use RefactoringChallenge\Ecommerce\Order\OrderCreationFailed;
use RefactoringChallenge\Ecommerce\Order\OrderItemsQuery;
use RefactoringChallenge\Ecommerce\Order\OrderLogsQuery;
use RefactoringChallenge\Ecommerce\Order\OrderNumberGenerator;
use RefactoringChallenge\Ecommerce\Order\OrderQuery;
use RefactoringChallenge\Ecommerce\Warehouse\InsufficientStock;
use RefactoringChallenge\Ecommerce\Warehouse\InventoryQuery;
use RefactoringChallenge\Ecommerce\Warehouse\ProductNotFound;
use RefactoringChallenge\Ecommerce\Warehouse\ProductQuery;

readonly final class PutOrderUseCase
{
    public function __construct(
        private LoggerInterface $logger,
        private ProductQuery $productQuery,
        private InventoryQuery $inventoryQuery,
        private CustomerQuery $customerQuery,
        private OrderQuery $orderQuery,
        private OrderItemsQuery $orderItemsQuery,
        private OrderLogsQuery $orderLogsQuery,
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
    public function handle(int $customerId, array $items, string $shippingAddress): int
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

            // TODO: lock mechanism as soon as the stock is available

            $totalAmount += MoneyCalculator::multiply($price, $item->quantity);
        }

        $orderId = $this->orderQuery->createOrder($customerId, $orderNumber, $totalAmount, $shippingAddress);

        foreach ($items as $item) {
            $product = $this->productQuery->getById($item->productId);

            $this->orderItemsQuery->addOrderItem(
                $orderId,
                $item->productId,
                $item->quantity,
                $product->price,
                MoneyCalculator::multiply($product->price, $item->quantity),
                $product->name,
                $product->sku,
            );

            $this->inventoryQuery->reserveStock($item->productId, $item->quantity);
        }

        $this->orderLogsQuery->logOrderCreated($orderId);

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
}
