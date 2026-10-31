<?php

namespace App\Services;

use App\Models\User;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BudgetOverview
{
    public function forMonth(User $user, CarbonImmutable $month, ?Collection $expenses = null): array
    {
        $expenses ??= $user->expenses()->inMonth($month)->get();
        $spentByCategory = $expenses->groupBy(fn ($expense) => $expense->category->value)
            ->map(fn ($entries) => $entries->sum(fn ($expense) => $expense->cents()));
        $budgets = $user->categoryBudgets()->inMonth($month)->orderBy('category')->get();
        $rows = $budgets->map(function ($budget) use ($spentByCategory) {
            $amount = $budget->cents();
            $spent = $spentByCategory->get($budget->category->value, 0);
            $remaining = $amount - $spent;
            $percentage = round($spent * 100 / $amount, 1);
            $tone = $spent * 100 >= $amount * 90 ? 'danger' : ($spent * 100 >= $amount * 70 ? 'warning' : 'safe');
            $warning = null;
            if ($spent > $amount) {
                $warning = 'Je hebt je budget voor '.$budget->category->label().' met '.Money::format(-$remaining).' overschreden.';
            } elseif ($spent === $amount) {
                $warning = 'Je hebt 100% van je budget voor '.$budget->category->label().' gebruikt.';
            } elseif ($spent * 100 >= $amount * 80) {
                $warning = 'Je hebt '.str_replace('.', ',', (string) $percentage).'% van je budget voor '.$budget->category->label().' gebruikt.';
            }

            return compact('budget', 'amount', 'spent', 'remaining', 'percentage', 'tone', 'warning');
        });
        $total = $rows->sum('amount');
        $spent = $rows->sum('spent');

        return compact('rows', 'total', 'spent') + [
            'remaining' => $total - $spent,
            'exceeded' => $rows->where('remaining', '<', 0)->count(),
            'unbudgeted' => $spentByCategory->sum() - $spent,
        ];
    }
}
