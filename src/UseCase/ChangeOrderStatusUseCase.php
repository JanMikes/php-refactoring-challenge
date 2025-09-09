<?php

declare(strict_types=1);

namespace RefactoringChallenge\UseCase;

use Psr\EventDispatcher\EventDispatcherInterface;
use RefactoringChallenge\Ecommerce\Order\OrderNotFound;
use RefactoringChallenge\Ecommerce\Order\OrderQuery;
use RefactoringChallenge\Ecommerce\Order\OrderStatus;
use RefactoringChallenge\Ecommerce\Order\OrderStatusAlreadyChanged;
use RefactoringChallenge\Ecommerce\Order\OrderStatusChanged;

readonly final class ChangeOrderStatusUseCase
{
    public function __construct(
        private OrderQuery $orderQuery,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @throws OrderNotFound
     * @throws OrderStatusAlreadyChanged
     */
    public function handle(int $orderId, OrderStatus $newStatus): void
    {
        $oldStatus = $this->orderQuery->getOrderStatus($orderId);

        if ($oldStatus === $newStatus) {
            throw new OrderStatusAlreadyChanged();
        }

        $this->orderQuery->changeOrderStatus($orderId, $newStatus);

        $this->eventDispatcher->dispatch(
            new OrderStatusChanged(
                orderId: $orderId,
                oldStatus: $oldStatus,
                newStatus: $newStatus
            ),
        );
    }
}