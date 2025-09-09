<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Tests\Ecommerce\Order;

use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\Order\OrderNotFound;
use RefactoringChallenge\Ecommerce\Order\OrderQuery;
use RefactoringChallenge\Ecommerce\Order\OrderStatus;
use RefactoringChallenge\Tech\DependencyInjection\ContainerFactory;
use RefactoringChallenge\Tests\TestingDatabase;

class OrderQueryTest extends TestCase
{
    private OrderQuery $orderQuery;

    protected function setUp(): void
    {
        $container = ContainerFactory::get();
        $this->orderQuery = $container->get(OrderQuery::class);

        TestingDatabase::prepareFreshData();
    }

    public function testGetOrderStatusReturnsCorrectStatus(): void
    {
        $orderId = 99;
        
        $status = $this->orderQuery->getOrderStatus($orderId);
        
        $this->assertEquals(OrderStatus::Pending, $status);
    }

    public function testGetOrderStatusThrowsExceptionForNonExistingOrder(): void
    {
        $nonExistingOrderId = 999999;
        
        $this->expectException(OrderNotFound::class);
        
        $this->orderQuery->getOrderStatus($nonExistingOrderId);
    }

    public function testChangeOrderStatusUpdatesStatus(): void
    {
        $orderId = 99;
        $newStatus = OrderStatus::Shipped;

        $actualStatus = $this->orderQuery->getOrderStatus($orderId);
        $this->assertEquals(OrderStatus::Pending, $actualStatus);

        $this->orderQuery->changeOrderStatus($orderId, $newStatus);
        
        $actualStatus = $this->orderQuery->getOrderStatus($orderId);
        $this->assertEquals($newStatus, $actualStatus);
    }

    public function testChangeOrderStatusThrowsExceptionForNonExistingOrder(): void
    {
        $nonExistingOrderId = 999999;
        $newStatus = OrderStatus::Shipped;
        
        $this->expectException(OrderNotFound::class);
        
        $this->orderQuery->changeOrderStatus($nonExistingOrderId, $newStatus);
    }
}