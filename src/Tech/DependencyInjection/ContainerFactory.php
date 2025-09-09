<?php

declare(strict_types=1);

namespace RefactoringChallenge\Tech\DependencyInjection;

use League\Container\Container;
use League\Container\ReflectionContainer;
use PDO;
use RefactoringChallenge\Tech\Database\PDOFactory;

class ContainerFactory
{
    private static null|Container $container = null;

    public static function create(): Container
    {
        $container = new Container();

        // Enable autowiring
        $container->delegate(new ReflectionContainer());

        $pdoFactory = new PDOFactory([
            'default' => [
                'host' => getenv('MYSQL_HOST') ?: $_ENV['MYSQL_HOST'],
                'database' => getenv('MYSQL_DATABASE') ?: $_ENV['MYSQL_DATABASE'],
                'user' => getenv('MYSQL_USER') ?: $_ENV['MYSQL_USER'],
                'password' => getenv('MYSQL_PASSWORD') ?: $_ENV['MYSQL_PASSWORD'],
            ],
        ]);

        $container->add(PDOFactory::class, $pdoFactory);
        $container->add(PDO::class, $pdoFactory->create());

        return $container;
    }

    public static function get(): Container
    {
        if (self::$container === null) {
            self::$container = self::create();
        }

        return self::$container;
    }
}