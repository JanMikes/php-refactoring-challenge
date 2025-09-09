<?php

declare(strict_types=1);

namespace RefactoringChallenge\Tests\UseCase;

use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\Order\DummyOrderQuery;
use RefactoringChallenge\Ecommerce\Order\OrderNotFound;
use RefactoringChallenge\Ecommerce\Order\OrderStatus;
use RefactoringChallenge\Ecommerce\Order\OrderStatusAlreadyChanged;
use RefactoringChallenge\Ecommerce\Order\OrderStatusChanged;
use RefactoringChallenge\Events\SpyEventDispatcher;
use RefactoringChallenge\UseCase\ChangeOrderStatusUseCase;

final class ChangeOrderStatusUseCaseTest extends TestCase
{
    private DummyOrderQuery $orderQuery;
    private SpyEventDispatcher $eventDispatcher;
    private ChangeOrderStatusUseCase $useCase;

    protected function setUp(): void
    {
        $this->orderQuery = new DummyOrderQuery();
        $this->eventDispatcher = new SpyEventDispatcher();
        $this->useCase = new ChangeOrderStatusUseCase($this->orderQuery, $this->eventDispatcher);
    }

    public function testHandleSuccessfullyChangesOrderStatus(): void
    {
        $orderId = 123;
        $oldStatus = OrderStatus::Pending;
        $newStatus = OrderStatus::Shipped;

        $this->orderQuery->addOrder($orderId, $oldStatus);

        $this->useCase->handle($orderId, $newStatus);

        self::assertSame($newStatus, $this->orderQuery->getOrderStatus($orderId));

        $events = $this->eventDispatcher->getDispatchedEventsOfType(OrderStatusChanged::class);
        self::assertCount(1, $events);
        self::assertSame($orderId, $events[0]->orderId);
        self::assertSame($oldStatus, $events[0]->oldStatus);
        self::assertSame($newStatus, $events[0]->newStatus);
    }

    public function testHandleThrowsOrderNotFoundWhenOrderDoesNotExist(): void
    {
        $orderId = 999;
        $newStatus = OrderStatus::Shipped;

        $this->expectException(OrderNotFound::class);

        $this->useCase->handle($orderId, $newStatus);
    }

    public function testHandleThrowsOrderStatusAlreadyChangedWhenStatusIsSame(): void
    {
        $orderId = 123;
        $status = OrderStatus::Pending;

        $this->orderQuery->addOrder($orderId, $status);

        $this->expectException(OrderStatusAlreadyChanged::class);

        $this->useCase->handle($orderId, $status);
    }

    public function testHandleDoesNotChangeStatusWhenStatusIsSame(): void
    {
        $orderId = 123;
        $status = OrderStatus::Pending;

        $this->orderQuery->addOrder($orderId, $status);

        try {
            $this->useCase->handle($orderId, $status);
        } catch (OrderStatusAlreadyChanged) {
        }

        self::assertSame($status, $this->orderQuery->getOrderStatus($orderId));

        $events = $this->eventDispatcher->getDispatchedEventsOfType(OrderStatusChanged::class);

        self::assertCount(0, $events);
    }
}