<?php

declare(strict_types=1);

namespace RefactoringChallenge\Tech\Database;

use PDO;

readonly final class PDOFactory
{
    /**
     * @param array<string, array{
     *     host: string,
     *     database: string,
     *     user: string,
     *     password: string,
     * }> $configurations
     */
    public function __construct(
        private array $configurations,
    ) {
    }

    public function create(string $connectionName = 'default'): PDO
    {
        $config = $this->configurations[$connectionName];

        $connection = new PDO(
            'mysql:host=' . $config['host'] . ';dbname=' . $config['database'],
            $config['user'],
            $config['password'],
        );

        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $connection;
    }
}