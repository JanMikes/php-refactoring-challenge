<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Warehouse;

use PDO;

readonly final class ProductQuery
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    /**
     * @throws ProductNotFound
     */
    public function getById(int $productId): Product
    {
        $stmt = $this->pdo->prepare("SELECT id, name, price, sku FROM products WHERE id = ?");
        $stmt->execute([$productId]);

        $productData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($productData === false) {
            throw new ProductNotFound($productId);
        }

        return new Product(
            id: (int) $productData['id'],
            name: $productData['name'],
            price: (float) $productData['price'],
            sku: $productData['sku'],
        );
    }

    /**
     * @throws ProductNotFound
     */
    public function getPrice(int $productId): float
    {
        $stmt = $this->pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$productId]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product === false) {
            throw new ProductNotFound($productId);
        }

        return (float) $product['price'];
    }
}