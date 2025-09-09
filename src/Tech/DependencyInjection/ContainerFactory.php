<?php

declare(strict_types=1);

namespace RefactoringChallenge\Tech\DependencyInjection;

use League\Container\Container;
use League\Container\ReflectionContainer;
use PDO;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RefactoringChallenge\Tech\Database\PDOFactory;

class ContainerFactory
{
    private static null|Container $container = null;

    public static function create(): Container
    {
        $container = new Container();

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
        $container->add(LoggerInterface::class, new NullLogger());

        // Enable autowiring and autoregistration for known instances
        $container->delegate(new ReflectionContainer());

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