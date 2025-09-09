<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Warehouse;

interface InventoryQuery
{
    /**
     * @throws ProductNotFound
     */
    public function getStock(int $productId): int;

    /**
     * @throws ProductNotFound
     */
    public function reserveStock(int $productId, int $quantity): void;
}