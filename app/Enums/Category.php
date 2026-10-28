<?php

namespace App\Enums;

enum Category: string
{
    case Housing = 'housing';
    case Insurance = 'insurance';
    case Subscriptions = 'subscriptions';
    case Telecom = 'telecom';
    case Transport = 'transport';
    case Loans = 'loans';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Housing => 'Wonen', self::Insurance => 'Verzekeringen',
            self::Subscriptions => 'Abonnementen', self::Telecom => 'Telefoon en internet',
            self::Transport => 'Vervoer', self::Loans => 'Leningen', self::Other => 'Overig',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Housing => 'house', self::Insurance => 'shield-check',
            self::Subscriptions => 'layers', self::Telecom => 'wifi',
            self::Transport => 'car', self::Loans => 'landmark', self::Other => 'shapes',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Housing => '#10b981', self::Insurance => '#6366f1',
            self::Subscriptions => '#f59e0b', self::Telecom => '#38bdf8',
            self::Transport => '#f472b6', self::Loans => '#a78bfa', self::Other => '#94a3b8',
        };
    }
}
