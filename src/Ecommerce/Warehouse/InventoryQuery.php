<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Warehouse;

use PDO;

readonly final class InventoryQuery
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    /**
     * @throws ProductNotFound
     */
    public function getStock(int $productId): int
    {
        $stmt = $this->pdo->prepare("SELECT quantity_available FROM inventory WHERE product_id = ?");
        $stmt->execute([$productId]);

        /**
         * @var false|array{
         *     quantity_available: int,
         * } $data
         */
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data === false) {
            throw new ProductNotFound($productId);
        }

        return $data['quantity_available'];
    }
}