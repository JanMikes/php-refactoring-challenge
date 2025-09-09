<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Tests\Ecommerce;

use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\InventoryQuery;
use RefactoringChallenge\Ecommerce\ProductNotFound;
use RefactoringChallenge\Tech\DependencyInjection\ContainerFactory;
use RefactoringChallenge\Tests\TestingDatabase;

class InventoryQueryTest extends TestCase
{
    private InventoryQuery $inventoryQuery;

    protected function setUp(): void
    {
        $container = ContainerFactory::get();
        $this->inventoryQuery = $container->get(InventoryQuery::class);

        TestingDatabase::prepareFreshData();
    }

    public function testGetStockReturnsCorrectQuantity()
    {
        $productId = 99;
        
        $stock = $this->inventoryQuery->getStock($productId);
        
        $this->assertIsInt($stock);
        $this->assertGreaterThanOrEqual(0, $stock);
    }

    public function testGetStockForNonExistingProduct()
    {
        $nonExistingProductId = 999999;

        $this->expectException(ProductNotFound::class);
        
        $this->inventoryQuery->getStock($nonExistingProductId);
    }
}