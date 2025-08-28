<?php

declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

print_r($_ENV['MYSQL_DATABASE']);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..', '.env.test');
$dotenv->safeLoad();

print_r($_ENV['MYSQL_DATABASE']);
die();
