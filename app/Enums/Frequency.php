<?php

namespace App\Enums;

enum Frequency: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'Wekelijks',
            self::Monthly => 'Maandelijks',
            self::Quarterly => 'Per kwartaal',
            self::Yearly => 'Jaarlijks',
        };
    }
}
