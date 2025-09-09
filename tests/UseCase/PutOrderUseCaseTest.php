<?php

declare(strict_types=1);

namespace RefactoringChallenge\Tests\UseCase;

use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\Cart\CartItem;
use RefactoringChallenge\Ecommerce\Order\DummyOrderItemsQuery;
use RefactoringChallenge\Ecommerce\Order\DummyOrderQuery;
use RefactoringChallenge\Ecommerce\Order\OrderCreated;
use RefactoringChallenge\Ecommerce\Order\OrderNumberGenerator;
use RefactoringChallenge\Ecommerce\Warehouse\DummyInventoryQuery;
use RefactoringChallenge\Ecommerce\Warehouse\DummyProductQuery;
use RefactoringChallenge\Ecommerce\Warehouse\InsufficientStock;
use RefactoringChallenge\Ecommerce\Warehouse\ProductNotFound;
use RefactoringChallenge\Events\SpyEventDispatcher;
use RefactoringChallenge\UseCase\PutOrderUseCase;

final class PutOrderUseCaseTest extends TestCase
{
    private DummyProductQuery $productQuery;
    private DummyInventoryQuery $inventoryQuery;
    private DummyOrderItemsQuery $orderItemsQuery;
    private SpyEventDispatcher $eventDispatcher;
    private PutOrderUseCase $useCase;

    protected function setUp(): void
    {
        $this->productQuery = new DummyProductQuery();
        $this->inventoryQuery = new DummyInventoryQuery();
        $orderQuery = new DummyOrderQuery();
        $this->orderItemsQuery = new DummyOrderItemsQuery();
        $orderNumberGenerator = new OrderNumberGenerator();
        $this->eventDispatcher = new SpyEventDispatcher();

        $this->useCase = new PutOrderUseCase(
            $this->productQuery,
            $this->inventoryQuery,
            $orderQuery,
            $this->orderItemsQuery,
            $orderNumberGenerator,
            $this->eventDispatcher,
        );
    }

    public function testHandleSuccessfullyCreatesOrderWithSingleItem(): void
    {
        $customerId = 1;
        $productId = 100;
        $quantity = 2;
        $price = 25.50;
        $shippingAddress = '123 Main St, City, Country';

        // Setup test data
        $this->productQuery->addProduct($productId, 'Test Product', $price, 'TEST-SKU');
        $this->inventoryQuery->addStock($productId, 10);

        $items = [new CartItem($productId, $quantity)];

        $orderId = $this->useCase->handle($customerId, $items, $shippingAddress);

        // Verify order was created
        self::assertTrue($orderId > 0);

        // Verify inventory was reserved
        self::assertSame(8, $this->inventoryQuery->getStock($productId)); // 10 - 2

        // Verify order items were added
        $orderItems = $this->orderItemsQuery->getOrderItems($orderId);
        self::assertCount(1, $orderItems);
        self::assertSame($orderId, $orderItems[0]['order_id']);
        self::assertSame($productId, $orderItems[0]['product_id']);
        self::assertSame($quantity, $orderItems[0]['quantity']);
        self::assertSame($price, $orderItems[0]['unit_price']);
        self::assertSame($price * $quantity, $orderItems[0]['total_price']);
        self::assertSame("Test Product", $orderItems[0]['product_name']);
        self::assertSame("TEST-SKU", $orderItems[0]['product_sku']);

        // Verify event was dispatched
        $events = $this->eventDispatcher->getDispatchedEventsOfType(OrderCreated::class);
        self::assertCount(1, $events);
        self::assertSame($orderId, $events[0]->orderId);
        self::assertSame($customerId, $events[0]->customerId);
    }

    public function testHandleSuccessfullyCreatesOrderWithMultipleItems(): void
    {
        $customerId = 2;
        $product1Id = 101;
        $product2Id = 102;
        $shippingAddress = '456 Oak Ave, City, Country';

        // Setup test data
        $this->productQuery->addProduct($product1Id, 'Product 1', 10.00, 'SKU-1');
        $this->productQuery->addProduct($product2Id, 'Product 2', 15.50, 'SKU-2');
        $this->inventoryQuery->addStock($product1Id, 5);
        $this->inventoryQuery->addStock($product2Id, 8);

        $items = [
            new CartItem($product1Id, 2),
            new CartItem($product2Id, 3),
        ];

        $orderId = $this->useCase->handle($customerId, $items, $shippingAddress);

        // Verify inventory was reserved for both products
        self::assertSame(3, $this->inventoryQuery->getStock($product1Id)); // 5 - 2
        self::assertSame(5, $this->inventoryQuery->getStock($product2Id)); // 8 - 3

        // Verify order items were added for both products
        $orderItems = $this->orderItemsQuery->getOrderItems($orderId);
        self::assertCount(2, $orderItems);

        // Sort by product_id to ensure consistent order
        usort($orderItems, fn($a, $b): int => $a['product_id'] <=> $b['product_id']);

        // Check first product
        self::assertSame($product1Id, $orderItems[0]['product_id']);
        self::assertSame(2, $orderItems[0]['quantity']);
        self::assertSame(10.00, $orderItems[0]['unit_price']);
        self::assertSame(20.00, $orderItems[0]['total_price']);

        // Check second product
        self::assertSame($product2Id, $orderItems[1]['product_id']);
        self::assertSame(3, $orderItems[1]['quantity']);
        self::assertSame(15.50, $orderItems[1]['unit_price']);
        self::assertSame(46.50, $orderItems[1]['total_price']);

        // Verify event was dispatched
        $events = $this->eventDispatcher->getDispatchedEventsOfType(OrderCreated::class);
        self::assertCount(1, $events);
        self::assertSame($orderId, $events[0]->orderId);
        self::assertSame($customerId, $events[0]->customerId);
    }

    public function testHandleThrowsProductNotFoundWhenProductDoesNotExistForPrice(): void
    {
        $customerId = 3;
        $productId = 999; // Non-existent product
        $items = [new CartItem($productId, 1)];
        $shippingAddress = 'Test Address';

        $this->expectException(ProductNotFound::class);

        $this->useCase->handle($customerId, $items, $shippingAddress);
    }

    public function testHandleThrowsProductNotFoundWhenProductDoesNotExistInInventory(): void
    {
        $customerId = 4;
        $productId = 103;
        $items = [new CartItem($productId, 1)];
        $shippingAddress = 'Test Address';

        // Add product but no inventory
        $this->productQuery->addProduct($productId, 'Test Product', 10.00, 'TEST-SKU');

        $this->expectException(ProductNotFound::class);

        $this->useCase->handle($customerId, $items, $shippingAddress);
    }

    public function testHandleThrowsInsufficientStockWhenNotEnoughStock(): void
    {
        $customerId = 5;
        $productId = 104;
        $requestedQuantity = 10;
        $availableStock = 5;
        $items = [new CartItem($productId, $requestedQuantity)];
        $shippingAddress = 'Test Address';

        // Setup test data
        $this->productQuery->addProduct($productId, 'Test Product', 20.00, 'TEST-SKU');
        $this->inventoryQuery->addStock($productId, $availableStock);

        $this->expectException(InsufficientStock::class);

        $this->useCase->handle($customerId, $items, $shippingAddress);
    }

    public function testHandleDoesNotDispatchEventWhenExceptionOccurs(): void
    {
        $customerId = 7;
        $productId = 999; // Non-existent product
        $items = [new CartItem($productId, 1)];
        $shippingAddress = 'Test Address';

        try {
            $this->useCase->handle($customerId, $items, $shippingAddress);
        } catch (ProductNotFound) {
            // Expected exception
        }

        // Verify no events were dispatched
        $events = $this->eventDispatcher->getDispatchedEventsOfType(OrderCreated::class);
        self::assertCount(0, $events);
    }

    public function testHandleCalculatesTotalAmountCorrectly(): void
    {
        $customerId = 8;
        $product1Id = 106;
        $product2Id = 107;
        $shippingAddress = 'Test Address';

        // Setup products with different prices
        $this->productQuery->addProduct($product1Id, 'Product 1', 12.50, 'SKU-1');
        $this->productQuery->addProduct($product2Id, 'Product 2', 7.25, 'SKU-2');
        $this->inventoryQuery->addStock($product1Id, 10);
        $this->inventoryQuery->addStock($product2Id, 10);

        $items = [
            new CartItem($product1Id, 3), // 3 * 12.50 = 37.50
            new CartItem($product2Id, 4), // 4 * 7.25 = 29.00
        ];

        $orderId = $this->useCase->handle($customerId, $items, $shippingAddress);

        $orderItems = $this->orderItemsQuery->getOrderItems($orderId);
        $totalFromItems = array_sum(array_column($orderItems, 'total_price'));
        self::assertSame(66.50, $totalFromItems);
    }
}