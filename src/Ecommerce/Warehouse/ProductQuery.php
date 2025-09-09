<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Warehouse;

interface ProductQuery
{
    /**
     * @throws ProductNotFound
     */
    public function getById(int $productId): Product;

    /**
     * @throws ProductNotFound
     */
    public function getPrice(int $productId): float;
}