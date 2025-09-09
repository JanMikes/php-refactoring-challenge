<?php

declare(strict_types=1);

namespace RefactoringChallenge\Events;

use Psr\EventDispatcher\EventDispatcherInterface;

final class SpyEventDispatcher implements EventDispatcherInterface
{
    /** @var array<object> */
    public array $dispatchedEvents = [];

    public function dispatch(object $event): object
    {
        $this->dispatchedEvents[] = $event;

        return $event;
    }

    /**
     * @template T
     *
     * @param class-string<T> $eventClass
     *
     * @return array<T>
     */
    public function getDispatchedEventsOfType(string $eventClass): array
    {
        return array_filter(
            array: $this->dispatchedEvents,
            callback: fn (object $event): bool => $event instanceof $eventClass,
        );
    }

    public function clear(): void
    {
        $this->dispatchedEvents = [];
    }
}