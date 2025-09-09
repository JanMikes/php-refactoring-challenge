<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Order;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Shipped = 'shipped';
}
