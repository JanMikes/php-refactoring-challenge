<?php

declare(strict_types=1);

namespace RefactoringChallenge\Events;

use RefactoringChallenge\Ecommerce\Order\OrderLogsQuery;
use RefactoringChallenge\Ecommerce\Order\OrderStatusChanged;

readonly final class LogStatusChangeWhenOrderStatusChanged
{
    public function __construct(
        private OrderLogsQuery $orderLogsQuery,
    ) {
    }

    public function __invoke(OrderStatusChanged $event): void
    {
        $this->orderLogsQuery->logStatusChange(
            orderId: $event->orderId,
            oldStatus: $event->oldStatus,
            newStatus: $event->newStatus,
        );
    }
}