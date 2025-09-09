<?php

declare(strict_types=1);

namespace RefactoringChallenge\Notification;

interface Notifier
{
    public function notifyUser(int $userId, string $text): void;

    public function notifyOrder(int $orderId, string $text): void;
}