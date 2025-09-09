<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Tests\Ecommerce;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RefactoringChallenge\Ecommerce\MoneyCalculator;

class MoneyCalculatorTest extends TestCase
{
    #[DataProvider('multiplyDataProvider')]
    public function testMultiply(float $price, int $quantity, int $expectedResult): void
    {
        $result = MoneyCalculator::multiply($price, $quantity);

        $this->assertEquals($expectedResult, $result);
    }

    public static function multiplyDataProvider(): array
    {
        return [
            'basic multiplication' => [10.50, 2, 2100],
            'single item' => [123.45, 1, 12345],
            'zero quantity' => [50.00, 0, 0],
            'zero price' => [0.0, 5, 0],
            'decimal precision test' => [1.234, 3, 369], // 1.234 * 100 = 123.4, rounded to 123, then * 3 = 369
            'small price' => [0.01, 100, 100],
            'large quantity' => [5.99, 1000, 599000],
            'rounding test 1' => [10.555, 2, 2112], // 10.555 * 100 = 1055.5, rounded to 1056, then * 2 = 2112
            'rounding test 2' => [10.554, 2, 2110], // 10.554 * 100 = 1055.4, rounded to 1055, then * 2 = 2110
            'negative price handling' => [-10.00, 3, -3000],
        ];
    }

    #[DataProvider('toFloatDataProvider')]
    public function testToFloat(int $amountInCents, float $expectedResult): void
    {
        $result = MoneyCalculator::toFloat($amountInCents);

        $this->assertEquals($expectedResult, $result);
    }

    public static function toFloatDataProvider(): array
    {
        return [
            'basic conversion' => [2100, 21.0],
            'single cent' => [1, 0.01],
            'zero amount' => [0, 0.0],
            'large amount' => [123456, 1234.56],
            'negative amount' => [-5000, -50.0],
            'precise decimal' => [12345, 123.45],
            'round number' => [10000, 100.0],
        ];
    }

    public function testMultiplyAndToFloatRoundTrip(): void
    {
        $originalPrice = 123.45;
        $quantity = 3;

        $amountInCents = MoneyCalculator::multiply($originalPrice, $quantity);
        $convertedBack = MoneyCalculator::toFloat($amountInCents);

        $expectedTotal = $originalPrice * $quantity;

        $this->assertEquals($expectedTotal, $convertedBack);
    }
}