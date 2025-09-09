<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Tests\Ecommerce\Warehouse;

use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\Warehouse\InventoryQuery;
use RefactoringChallenge\Ecommerce\Warehouse\ProductNotFound;
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
}