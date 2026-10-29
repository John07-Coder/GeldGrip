<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case Groceries = 'groceries';
    case Dining = 'dining';
    case Clothing = 'clothing';
    case Transport = 'transport';
    case Entertainment = 'entertainment';
    case Shopping = 'shopping';
    case Health = 'health';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Groceries => 'Boodschappen', self::Dining => 'Uit eten',
            self::Clothing => 'Kleding', self::Transport => 'Vervoer',
            self::Entertainment => 'Entertainment', self::Shopping => 'Winkelen',
            self::Health => 'Gezondheid', self::Other => 'Overig',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Groceries => 'shopping-basket', self::Dining => 'utensils',
            self::Clothing => 'shirt', self::Transport => 'car',
            self::Entertainment => 'clapperboard', self::Shopping => 'shopping-bag',
            self::Health => 'heart-pulse', self::Other => 'shapes',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Groceries => '#10b981', self::Dining => '#f59e0b',
            self::Clothing => '#a78bfa', self::Transport => '#38bdf8',
            self::Entertainment => '#f472b6', self::Shopping => '#818cf8',
            self::Health => '#2dd4bf', self::Other => '#94a3b8',
        };
    }
}
