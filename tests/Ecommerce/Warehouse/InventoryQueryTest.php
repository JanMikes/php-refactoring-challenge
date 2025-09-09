<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Tests\Ecommerce\Warehouse;

use PDO;
use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\Warehouse\InventoryQuery;
use RefactoringChallenge\Ecommerce\Warehouse\ProductNotFound;
use RefactoringChallenge\Tech\DependencyInjection\ContainerFactory;
use RefactoringChallenge\Tests\TestingDatabase;

class InventoryQueryTest extends TestCase
{
    private InventoryQuery $inventoryQuery;
    private PDO $pdo;

    protected function setUp(): void
    {
        $container = ContainerFactory::get();
        $this->inventoryQuery = $container->get(InventoryQuery::class);
        $this->pdo = $container->get(PDO::class);

        TestingDatabase::prepareFreshData();
    }

    public function testGetStockReturnsCorrectQuantity(): void
    {
        $productId = 99;

        $stock = $this->inventoryQuery->getStock($productId);

        $this->assertEquals(10, $stock);
    }

    public function testGetStockForNonExistingProduct(): void
    {
        $nonExistingProductId = 999999;

        $this->expectException(ProductNotFound::class);

        $this->inventoryQuery->getStock($nonExistingProductId);
    }

    public function testReserveStockUpdatesQuantities(): void
    {
        $productId = 99;
        $quantityToReserve = 3;
        
        // Get initial quantities
        $initialStock = $this->inventoryQuery->getStock($productId);
        
        $stmt = $this->pdo->prepare("SELECT quantity_reserved FROM inventory WHERE product_id = ?");
        $stmt->execute([$productId]);
        $initialReserved = (int) $stmt->fetchColumn();
        
        // Reserve stock
        $this->inventoryQuery->reserveStock($productId, $quantityToReserve);
        
        // Verify updated quantities
        $newStock = $this->inventoryQuery->getStock($productId);
        
        $stmt = $this->pdo->prepare("SELECT quantity_reserved FROM inventory WHERE product_id = ?");
        $stmt->execute([$productId]);
        $newReserved = (int) $stmt->fetchColumn();
        
        $this->assertEquals($initialStock - $quantityToReserve, $newStock);
        $this->assertEquals($initialReserved + $quantityToReserve, $newReserved);
    }

    public function testReserveStockThrowsExceptionForNonExistingProduct(): void
    {
        $nonExistingProductId = 999999;
        $quantityToReserve = 1;
        
        $this->expectException(ProductNotFound::class);
        
        $this->inventoryQuery->reserveStock($nonExistingProductId, $quantityToReserve);
    }
}