<?php

declare(strict_types=1);

namespace RefactoringChallenge\Tests\UseCase;

use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\Order\OrderNotFound;
use RefactoringChallenge\Ecommerce\Order\OrderQuery;
use RefactoringChallenge\Ecommerce\Order\OrderStatus;
use RefactoringChallenge\Ecommerce\Order\OrderStatusAlreadyChanged;
use RefactoringChallenge\Tech\DependencyInjection\ContainerFactory;
use RefactoringChallenge\Tests\TestingDatabase;
use RefactoringChallenge\UseCase\ChangeOrderStatusUseCase;

class ChangeOrderStatusUseCaseIntegrationTest extends TestCase
{
    private ChangeOrderStatusUseCase $useCase;
    private OrderQuery $orderQuery;

    protected function setUp(): void
    {
        $container = ContainerFactory::get();
        $this->useCase = $container->get(ChangeOrderStatusUseCase::class);
        $this->orderQuery = $container->get(OrderQuery::class);

        TestingDatabase::prepareFreshData();
    }

    public function testHandleChangesOrderStatusSuccessfully(): void
    {
        $orderId = 99;
        $newStatus = OrderStatus::Shipped;
        
        $initialStatus = $this->orderQuery->getOrderStatus($orderId);
        $this->assertEquals(OrderStatus::Pending, $initialStatus);
        
        $this->useCase->handle($orderId, $newStatus);
        
        $updatedStatus = $this->orderQuery->getOrderStatus($orderId);
        $this->assertEquals($newStatus, $updatedStatus);
    }

    public function testHandleThrowsExceptionWhenStatusIsAlreadyTheSame(): void
    {
        $orderId = 99;
        $currentStatus = $this->orderQuery->getOrderStatus($orderId);
        
        $this->expectException(OrderStatusAlreadyChanged::class);
        
        $this->useCase->handle($orderId, $currentStatus);
    }

    public function testHandleThrowsExceptionForNonExistentOrder(): void
    {
        $nonExistentOrderId = 999999;
        $newStatus = OrderStatus::Shipped;
        
        $this->expectException(OrderNotFound::class);
        
        $this->useCase->handle($nonExistentOrderId, $newStatus);
    }
}