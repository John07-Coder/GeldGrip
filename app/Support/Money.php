<?php

namespace App\Support;

use App\Enums\Frequency;
use InvalidArgumentException;

final class Money
{
    public static function cents(string $amount): int
    {
        if (! preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D', $amount)) {
            throw new InvalidArgumentException('Gebruik een positief bedrag met maximaal twee decimalen.');
        }

        [$euros, $decimals] = array_pad(explode('.', $amount), 2, '');

        return (int) $euros * 100 + (int) str_pad($decimals, 2, '0');
    }

    public static function monthly(string $amount, Frequency $frequency): int
    {
        $cents = self::cents($amount);
        [$numerator, $denominator] = match ($frequency) {
            Frequency::Weekly => [52, 12], Frequency::Monthly => [1, 1],
            Frequency::Quarterly => [1, 3], Frequency::Yearly => [1, 12],
        };

        // Round each monthly average once, half up, using only integer arithmetic.
        return intdiv($cents * $numerator + intdiv($denominator, 2), $denominator);
    }

    public static function format(int $cents): string
    {
        $absolute = abs($cents);
        $euros = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', (string) intdiv($absolute, 100));

        return ($cents < 0 ? '− ' : '').'€ '.$euros.','.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);
    }
}
