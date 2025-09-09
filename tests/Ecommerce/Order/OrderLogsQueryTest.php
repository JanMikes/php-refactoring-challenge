<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Tests\Ecommerce\Order;

use PDO;
use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\Order\OrderLogsQuery;
use RefactoringChallenge\Ecommerce\Order\OrderStatus;
use RefactoringChallenge\Tech\DependencyInjection\ContainerFactory;
use RefactoringChallenge\Tests\TestingDatabase;

class OrderLogsQueryTest extends TestCase
{
    private OrderLogsQuery $orderLogsQuery;
    private PDO $pdo;

    protected function setUp(): void
    {
        $container = ContainerFactory::get();
        $this->orderLogsQuery = $container->get(OrderLogsQuery::class);
        $this->pdo = $container->get(PDO::class);

        TestingDatabase::prepareFreshData();
    }

    public function testLogOrderCreatedInsertsCorrectLog(): void
    {
        $orderId = 99;
        
        $this->orderLogsQuery->logOrderCreated($orderId);
        
        // Verify the log was inserted
        $stmt = $this->pdo->prepare("SELECT * FROM order_logs WHERE order_id = ? AND action = 'created'");
        $stmt->execute([$orderId]);
        
        /**
         * @var false|array{
         *     order_id: int,
         *     action: string,
         *     new_status: string,
         *     description: string,
         * } $log
         */
        $log = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotFalse($log);
        $this->assertEquals($orderId, $log['order_id']);
        $this->assertEquals('created', $log['action']);
        $this->assertEquals('pending', $log['new_status']);
        $this->assertEquals('Order created', $log['description']);
    }

    public function testLogStatusChangeInsertsCorrectLog(): void
    {
        $orderId = 99;
        $oldStatus = OrderStatus::Pending;
        $newStatus = OrderStatus::Shipped;
        
        $this->orderLogsQuery->logStatusChange($orderId, $oldStatus, $newStatus);
        
        // Verify the log was inserted
        $stmt = $this->pdo->prepare("SELECT * FROM order_logs WHERE order_id = ? AND action = 'status_change'");
        $stmt->execute([$orderId]);
        
        /**
         * @var false|array{
         *     order_id: int,
         *     action: string,
         *     old_status: string,
         *     new_status: string,
         *     description: string,
         * } $log
         */
        $log = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotFalse($log);
        $this->assertEquals($orderId, $log['order_id']);
        $this->assertEquals('status_change', $log['action']);
        $this->assertEquals($oldStatus->value, $log['old_status']);
        $this->assertEquals($newStatus->value, $log['new_status']);
        $this->assertEquals('Status updated', $log['description']);
    }
}