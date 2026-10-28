<?php

namespace App\Services;

use App\Enums\Category;
use App\Models\User;
use Carbon\CarbonImmutable;

class FinancialOverview
{
    public function forMonth(User $user, CarbonImmutable $month): array
    {
        $incomes = $user->incomeSources()->relevantTo($month)->orderBy('name')->get();
        $expenses = $user->fixedExpenses()->relevantTo($month)->orderBy('name')->get();
        $income = $incomes->sum(fn ($entry) => $entry->monthlyCents());
        $costs = $expenses->sum(fn ($entry) => $entry->monthlyCents());
        $categories = collect(Category::cases())->map(fn ($category) => [
            'label' => $category->label(), 'color' => $category->color(),
            'cents' => $expenses->where('category', $category)->sum(fn ($entry) => $entry->monthlyCents()),
        ])->filter(fn ($category) => $category['cents'] > 0)->values();

        return compact('incomes', 'expenses', 'income', 'costs', 'categories') + [
            'remaining' => $income - $costs,
            'percentage' => $income > 0 ? round(($income - $costs) * 100 / $income, 1) : null,
            'largest' => $expenses->sortByDesc(fn ($entry) => $entry->monthlyCents())->first(),
        ];
    }
}
