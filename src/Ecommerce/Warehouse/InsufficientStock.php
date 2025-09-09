<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Warehouse;

use Throwable;

class InsufficientStock extends \Exception
{
    public function __construct(
        readonly public int $productId,
        readonly public int $requestedQuantity,
        readonly public int $stockAvailable,
        string $message = '',
        int $code = 0,
        null|Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}