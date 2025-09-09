<?php

declare(strict_types=1);

namespace RefactoringChallenge\Order;

use PDO;

readonly final class OrderQuery
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    /**
     * @throws ProductNotFound
     */
    public function getProductPrice(int $productId): float
    {
        $stmt = $this->pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$productId]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new ProductNotFound($productId);
        }

        return (float) $product['price'];
    }
}