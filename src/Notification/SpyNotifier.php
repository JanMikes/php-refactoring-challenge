<?php

declare(strict_types=1);

namespace RefactoringChallenge\Notification;

final class SpyNotifier implements Notifier
{
    /**
     * @var array<int, array<string>>
     */
    public array $userNotifications = [];

    /**
     * @var array<int, array<string>>
     */
    public array $orderNotifications = [];

    public function notifyUser(int $userId, string $text): void
    {
        $this->userNotifications[$userId][] = $text;
    }

    public function notifyOrder(int $orderId, string $text): void
    {
        $this->orderNotifications[$orderId][] = $text;
    }
}