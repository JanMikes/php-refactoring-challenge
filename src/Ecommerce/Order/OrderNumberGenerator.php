<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Order;

readonly final class OrderNumberGenerator
{
    public function next(): string
    {
        return 'ORD-' . date('Y') . '-' . rand(1000, 9999);
    }
}