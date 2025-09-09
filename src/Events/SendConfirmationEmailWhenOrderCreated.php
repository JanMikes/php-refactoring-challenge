<?php

declare(strict_types=1);

namespace RefactoringChallenge\Events;

use Psr\Log\LoggerInterface;
use RefactoringChallenge\Ecommerce\Customer\CustomerNotFound;
use RefactoringChallenge\Ecommerce\Customer\CustomerQuery;
use RefactoringChallenge\Ecommerce\Order\OrderCreated;

readonly final class SendConfirmationEmailWhenOrderCreated
{
    public function __construct(
        private LoggerInterface $logger,
        private CustomerQuery $customerQuery,
    ) {
    }

    /**
     * @throws CustomerNotFound
     */
    public function __invoke(OrderCreated $event): void
    {
        $customer = $this->customerQuery->getById($event->customerId);

        $this->logger->info('Sending email', [
            'email' => $customer->email,
            'orderId' => $event->orderId,
        ]);
    }
}