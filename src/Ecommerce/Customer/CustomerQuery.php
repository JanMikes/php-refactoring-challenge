<?php

declare(strict_types=1);

namespace RefactoringChallenge\Ecommerce\Customer;

use PDO;

readonly final class CustomerQuery
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    /**
     * @throws CustomerNotFound
     */
    public function getById(int $customerId): Customer
    {
        $stmt = $this->pdo->prepare("SELECT id, email, first_name FROM customers WHERE id = ?");
        $stmt->execute([$customerId]);

        /**
         * @var false|array{
         *     id: int,
         *     email: string,
         *     first_name: string,
         * } $data
         */
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data === false) {
            throw new CustomerNotFound($customerId);
        }

        return new Customer(
            id: $data['id'],
            email: $data['email'],
            firstName: $data['first_name'],
        );
    }
}