<?php

declare(strict_types = 1);

namespace RefactoringChallenge\UseCase;

use Psr\EventDispatcher\EventDispatcherInterface;
use RefactoringChallenge\Ecommerce\Cart\CartItem;
use RefactoringChallenge\Ecommerce\MoneyCalculator;
use RefactoringChallenge\Ecommerce\Order\OrderCreated;
use RefactoringChallenge\Ecommerce\Order\OrderCreationFailed;
use RefactoringChallenge\Ecommerce\Order\OrderItemsQuery;
use RefactoringChallenge\Ecommerce\Order\OrderNumberGenerator;
use RefactoringChallenge\Ecommerce\Order\OrderQuery;
use RefactoringChallenge\Ecommerce\Warehouse\InsufficientStock;
use RefactoringChallenge\Ecommerce\Warehouse\InventoryQuery;
use RefactoringChallenge\Ecommerce\Warehouse\ProductNotFound;
use RefactoringChallenge\Ecommerce\Warehouse\ProductQuery;

readonly final class PutOrderUseCase
{
    public function __construct(
        private ProductQuery $productQuery,
        private InventoryQuery $inventoryQuery,
        private OrderQuery $orderQuery,
        private OrderItemsQuery $orderItemsQuery,
        private OrderNumberGenerator $orderNumberGenerator,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param list<CartItem> $items
     *
     * @throws ProductNotFound
     * @throws InsufficientStock
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

        // TODO: Transaction start
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
        // TODO: Transaction end

        $this->eventDispatcher->dispatch(
            new OrderCreated(
                orderId: $orderId,
                customerId: $customerId,
            ),
        );

        return $orderId;
    }
}
