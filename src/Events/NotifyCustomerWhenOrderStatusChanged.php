<?php

declare(strict_types=1);

namespace RefactoringChallenge\Events;

use RefactoringChallenge\Ecommerce\Order\OrderStatus;
use RefactoringChallenge\Ecommerce\Order\OrderStatusChanged;
use RefactoringChallenge\Notification\Notifier;

readonly final class NotifyCustomerWhenOrderStatusChanged
{
    public function __construct(
        private Notifier $notifier
    ) {
    }

    public function __invoke(OrderStatusChanged $event): void
    {
        if ($event->newStatus !== OrderStatus::Shipped) {
            return;
        }

        $this->notifier->notifyOrder($event->orderId, 'Sending shipping notification');
    }
}