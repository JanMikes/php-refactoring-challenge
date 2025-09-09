<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce;

use Exception;
use Throwable;

class ProductNotFound extends Exception
{
    public function __construct(
        readonly public int $id,
        string $message = '',
        int $code = 0,
        null|Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}