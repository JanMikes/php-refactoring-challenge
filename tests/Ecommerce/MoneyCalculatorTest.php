<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Tests\Ecommerce;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\MoneyCalculator;

class MoneyCalculatorTest extends TestCase
{
    #[DataProvider('multiplyDataProvider')]
    public function testMultiply(float $price, int $quantity, float $expectedResult): void
    {
        $result = MoneyCalculator::multiply($price, $quantity);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array<string, array{float, int, float}>
     */
    public static function multiplyDataProvider(): array
    {
        return [
            'basic multiplication' => [10.50, 2, 21.00],
            'single item' => [123.45, 1, 123.45],
            'zero quantity' => [50.00, 0, 0.0],
            'zero price' => [0.0, 5, 0],
            'decimal precision test' => [1.234, 3, 3.69],
            'small price' => [0.01, 100, 1.00],
            'large quantity' => [5.99, 1000, 5990.00],
            'rounding test 1' => [10.555, 2, 21.12],
            'rounding test 2' => [10.554, 2, 21.10],
            'negative price handling' => [-10.00, 3, -30.00],
        ];
    }
}