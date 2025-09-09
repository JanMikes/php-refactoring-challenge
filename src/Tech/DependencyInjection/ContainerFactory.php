<?php

declare(strict_types=1);

namespace RefactoringChallenge\Tech\DependencyInjection;

use League\Container\Container;
use League\Container\ReflectionContainer;
use League\Event\EventDispatcher;
use PDO;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RefactoringChallenge\Ecommerce\Order\OrderCreated;
use RefactoringChallenge\Ecommerce\Order\OrderStatusChanged;
use RefactoringChallenge\Events\LogOrderWhenOrderCreated;
use RefactoringChallenge\Events\LogStatusChangeWhenOrderStatusChanged;
use RefactoringChallenge\Events\NotifyCustomerWhenOrderStatusChanged;
use RefactoringChallenge\Events\SendConfirmationEmailWhenOrderCreated;
use RefactoringChallenge\Notification\Notifier;
use RefactoringChallenge\Notification\SpyNotifier;
use RefactoringChallenge\Tech\Database\PDOFactory;
use RefactoringChallenge\Ecommerce\Order\OrderQuery;
use RefactoringChallenge\Ecommerce\Order\PDOOrderQuery;

class ContainerFactory
{
    private static null|Container $container = null;

    public static function create(): Container
    {
        $container = new Container();

        // Enable autowiring and autoregistration for known instances
        $container->delegate(new ReflectionContainer());

        $pdoFactory = new PDOFactory([
            'default' => [
                'host' => self::getEnvAsString('MYSQL_HOST'),
                'database' => self::getEnvAsString('MYSQL_DATABASE'),
                'user' => self::getEnvAsString('MYSQL_USER'),
                'password' => self::getEnvAsString('MYSQL_PASSWORD'),
            ],
        ]);

        $container->add(PDOFactory::class, $pdoFactory);
        $container->add(PDO::class, $pdoFactory->create());

        $container->add(LoggerInterface::class, NullLogger::class);
        $container->add(Notifier::class, SpyNotifier::class);

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->subscribeTo(OrderCreated::class, $container->get(LogOrderWhenOrderCreated::class));
        $eventDispatcher->subscribeTo(OrderCreated::class, $container->get(SendConfirmationEmailWhenOrderCreated::class));
        $eventDispatcher->subscribeTo(OrderStatusChanged::class, $container->get(LogStatusChangeWhenOrderStatusChanged::class));
        $eventDispatcher->subscribeTo(OrderStatusChanged::class, $container->get(NotifyCustomerWhenOrderStatusChanged::class));

        $container->add(EventDispatcher::class, $eventDispatcher);
        $container->add(EventDispatcherInterface::class, $eventDispatcher);

        $container->add(OrderQuery::class, $container->get(PDOOrderQuery::class));

        return $container;
    }

    public static function get(): Container
    {
        if (self::$container === null) {
            self::$container = self::create();
        }

        return self::$container;
    }

    private static function getEnvAsString(string $envName): string
    {
        /** @var null|string|int|float $env */
        $env = getenv($envName) ?: $_ENV[$envName];

        return (string) $env;
    }
}