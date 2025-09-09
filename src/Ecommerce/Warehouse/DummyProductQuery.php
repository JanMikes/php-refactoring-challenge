<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Warehouse;

final class DummyProductQuery implements ProductQuery
{
    /**
     * @var array<int, Product>
     */
    private array $products = [];

    public function getById(int $productId): Product
    {
        if (!isset($this->products[$productId])) {
            throw new ProductNotFound($productId);
        }

        return $this->products[$productId];
    }

    public function getPrice(int $productId): float
    {
        if (!isset($this->products[$productId])) {
            throw new ProductNotFound($productId);
        }

        return $this->products[$productId]->price;
    }

    public function addProduct(int $id, string $name, float $price, string $sku): void
    {
        $this->products[$id] = new Product($id, $name, $price, $sku);
    }
}