<?php

declare(strict_types = 1);

namespace RefactoringChallenge\Ecommerce;

final class MoneyCalculator
{
    private const SCALE = 100;

    public static function multiply(float $price, int $quantity): float
    {
        $priceInCents = (int) round($price * self::SCALE);
        
        return self::toFloat($priceInCents * $quantity);
    }

    private static function toFloat(int $amountInCents): float
    {
        return $amountInCents / self::SCALE;
    }
}