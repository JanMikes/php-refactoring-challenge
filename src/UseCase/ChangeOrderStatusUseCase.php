<?php

declare(strict_types=1);

namespace RefactoringChallenge\UseCase;

use RefactoringChallenge\Ecommerce\Order\OrderLogsQuery;
use RefactoringChallenge\Ecommerce\Order\OrderNotFound;
use RefactoringChallenge\Ecommerce\Order\OrderQuery;
use RefactoringChallenge\Ecommerce\Order\OrderStatus;
use RefactoringChallenge\Ecommerce\Order\OrderStatusAlreadyChanged;
use RefactoringChallenge\Notification\Notifier;

readonly final class ChangeOrderStatusUseCase
{
    public function __construct(
        private OrderQuery $orderQuery,
        private OrderLogsQuery $orderLogsQuery,
        private Notifier $notifier,
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

        // In real system, there could be domain event StatusChanged
        // That would handle both logging and sending notification

        $this->orderLogsQuery->logStatusChange($orderId, $oldStatus, $newStatus);

        if ($newStatus === OrderStatus::Shipped) {
            $this->notifier->notifyOrder($orderId, 'Sending shipping notification');
        }
    }
}