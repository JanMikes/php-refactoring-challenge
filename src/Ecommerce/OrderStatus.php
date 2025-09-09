<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Shipped = 'shipped';
}
