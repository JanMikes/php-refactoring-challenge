<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Warehouse;

use PDO;

readonly final class PDOProductQuery implements ProductQuery
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

        /**
         * @var false|array{
         *     id: int,
         *     name: string,
         *     price: numeric-string,
         *     sku: string,
         * } $data
         */
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data === false) {
            throw new ProductNotFound($productId);
        }

        return new Product(
            id: $data['id'],
            name: $data['name'],
            price: (float) $data['price'],
            sku: $data['sku'],
        );
    }

    /**
     * @throws ProductNotFound
     */
    public function getPrice(int $productId): float
    {
        $stmt = $this->pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$productId]);

        /**
         * @var false|array{
         *     price: numeric-string,
         * } $data
         */
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data === false) {
            throw new ProductNotFound($productId);
        }

        return (float) $data['price'];
    }
}