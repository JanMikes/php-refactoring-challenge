<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce;

use PDO;

readonly final class InventoryQuery
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    public function getStock(int $productId): int
    {
        $stmt = $this->pdo->prepare("SELECT quantity_available FROM inventory WHERE product_id = ?");
        $stmt->execute([$productId]);
        $inventory = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $inventory['quantity_available'];
    }
}