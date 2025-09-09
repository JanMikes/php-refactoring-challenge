<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Tests\Ecommerce;

use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\ProductNotFound;
use RefactoringChallenge\Ecommerce\ProductQuery;
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

    public function testGetPriceReturnsCorrectPrice()
    {
        $productId = 99;
        
        $price = $this->productQuery->getPrice($productId);

        $this->assertEquals(123.45, $price);
    }

    public function testGetPriceThrowsExceptionForNonExistingProduct()
    {
        $nonExistingProductId = 999999;
        
        $this->expectException(ProductNotFound::class);
        
        $this->productQuery->getPrice($nonExistingProductId);
    }
}