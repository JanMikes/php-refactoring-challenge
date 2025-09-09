<?php

declare(strict_types=1);

namespace RefactoringChallenge\Events;

use RefactoringChallenge\Ecommerce\Order\OrderCreated;
use RefactoringChallenge\Ecommerce\Order\OrderLogsQuery;

readonly final class LogOrderWhenOrderCreated
{
    public function __construct(
        private OrderLogsQuery $orderLogsQuery,
    ) {
    }

    public function __invoke(OrderCreated $event): void
    {
        $this->orderLogsQuery->logOrderCreated($event->orderId);
    }
}
