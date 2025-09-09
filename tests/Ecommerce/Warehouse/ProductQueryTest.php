<?php

declare(strict_types = 1);

namespace Ecommerce\Warehouse;

use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\Warehouse\ProductNotFound;
use RefactoringChallenge\Ecommerce\Warehouse\ProductQuery;
use RefactoringChallenge\Tech\DependencyInjection\ContainerFactory;
use RefactoringChallenge\Tests\TestingDatabase;

class ProductQueryTest extends TestCase
{
    private ProductQuery $productQuery;

    protected function setUp(): void
    {
        $container = ContainerFactory::get();
        $this->productQuery = $container->get(ProductQuery::class);

        TestingDatabase::prepareFreshData();
    }

    public function testGetPriceReturnsCorrectPrice(): void
    {
        $productId = 99;
        
        $price = $this->productQuery->getPrice($productId);

        $this->assertEquals(123.45, $price);
    }

    public function testGetPriceThrowsExceptionForNonExistingProduct(): void
    {
        $nonExistingProductId = 999999;
        
        $this->expectException(ProductNotFound::class);
        
        $this->productQuery->getPrice($nonExistingProductId);
    }

    public function testGetByIdReturnsCorrectProduct(): void
    {
        $productId = 99;
        
        $product = $this->productQuery->getById($productId);
        
        $this->assertEquals($productId, $product->id);
        $this->assertEquals('Test Produkt', $product->name);
        $this->assertEquals(123.45, $product->price);
        $this->assertEquals('TEST-99', $product->sku);
    }

    public function testGetByIdThrowsExceptionForNonExistingProduct(): void
    {
        $nonExistingProductId = 999999;
        
        $this->expectException(ProductNotFound::class);
        
        $this->productQuery->getById($nonExistingProductId);
    }
}