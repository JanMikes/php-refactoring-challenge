<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Customer;

readonly final class Customer
{
    public function __construct(
        public int $id,
        public string $email,
        public string $firstName,
    ) {
    }
}