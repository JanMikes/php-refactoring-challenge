<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Warehouse;

final class DummyInventoryQuery implements InventoryQuery
{
    /**
     * @var array<int, array{available: int, reserved: int}>
     */
    private array $inventory = [];

    public function getStock(int $productId): int
    {
        if (!isset($this->inventory[$productId])) {
            throw new ProductNotFound($productId);
        }

        return $this->inventory[$productId]['available'];
    }

    public function reserveStock(int $productId, int $quantity): void
    {
        if (!isset($this->inventory[$productId])) {
            throw new ProductNotFound($productId);
        }

        $this->inventory[$productId]['available'] -= $quantity;
        $this->inventory[$productId]['reserved'] += $quantity;
    }

    public function addStock(int $productId, int $available, int $reserved = 0): void
    {
        $this->inventory[$productId] = [
            'available' => $available,
            'reserved' => $reserved,
        ];
    }
}