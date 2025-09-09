<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Tests\Ecommerce\Customer;

use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\Customer\CustomerNotFound;
use RefactoringChallenge\Ecommerce\Customer\CustomerQuery;
use RefactoringChallenge\Tech\DependencyInjection\ContainerFactory;
use RefactoringChallenge\Tests\TestingDatabase;

class CustomerQueryTest extends TestCase
{
    private CustomerQuery $customerQuery;

    protected function setUp(): void
    {
        $container = ContainerFactory::get();
        $this->customerQuery = $container->get(CustomerQuery::class);

        TestingDatabase::prepareFreshData();
    }

    public function testGetByIdReturnsCorrectCustomer(): void
    {
        $customerId = 99;

        $customer = $this->customerQuery->getById($customerId);

        $this->assertEquals($customerId, $customer->id);
        $this->assertEquals('test@example.com', $customer->email);
        $this->assertEquals('Tester', $customer->firstName);
    }

    public function testGetByIdThrowsExceptionForNonExistingCustomer(): void
    {
        $nonExistingCustomerId = 999999;

        $this->expectException(CustomerNotFound::class);

        $this->customerQuery->getById($nonExistingCustomerId);
    }
}