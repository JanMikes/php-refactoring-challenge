<?php

declare(strict_types=1);

namespace RefactoringChallenge\Tech\Database;

use PDO;

readonly final class PDOFactory
{
    public function create(): PDO
    {
        // If we needed to work with multiple connections, then create($connectionName) and accepting credentials for multiple would be needed

        $connection = new PDO(
            'mysql:host=' . (getenv('MYSQL_HOST') ?: $_ENV['MYSQL_HOST']) . ';dbname=' . (getenv('MYSQL_DATABASE') ?: $_ENV['MYSQL_DATABASE']),
            getenv('MYSQL_USER') ?: $_ENV['MYSQL_USER'],
            getenv('MYSQL_PASSWORD') ?: $_ENV['MYSQL_PASSWORD']
        );

        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $connection;
    }
}