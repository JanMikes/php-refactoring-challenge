<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Ecommerce;

final class MoneyCalculator
{
    private const SCALE = 100;

    public static function multiply(float $price, int $quantity): int
    {
        $priceInCents = (int) round($price * self::SCALE);
        
        return $priceInCents * $quantity;
    }

    public static function toFloat(int $amountInCents): float
    {
        return $amountInCents / self::SCALE;
    }
}