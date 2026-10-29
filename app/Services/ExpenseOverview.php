<?php

namespace App\Services;

use App\Enums\ExpenseCategory;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ExpenseOverview
{
    public function summarize(Collection $expenses): array
    {
        $total = $expenses->sum(fn ($expense) => $expense->cents());
        $count = $expenses->count();
        $categories = collect(ExpenseCategory::cases())->map(fn ($category) => [
            'key' => $category->value, 'label' => $category->label(), 'color' => $category->color(),
            'cents' => $expenses->where('category', $category)->sum(fn ($expense) => $expense->cents()),
        ]);

        return compact('total', 'count', 'categories') + [
            'average' => $count ? intdiv($total + intdiv($count, 2), $count) : 0,
            'largestCategory' => $categories->where('cents', '>', 0)->sortByDesc('cents')->first(),
        ];
    }

    public function forMonth(User $user, CarbonImmutable $month): array
    {
        $start = $month->startOfMonth()->subMonths(5);
        $expenses = $user->expenses()->where('expense_date', '>=', $start->toDateString())
            ->where('expense_date', '<', $month->startOfMonth()->addMonth()->toDateString())->get();
        $groups = $expenses->groupBy(fn ($expense) => $expense->expense_date->format('Y-m'));
        $trend = collect(range(0, 5))->map(function ($offset) use ($start, $groups) {
            $period = $start->addMonths($offset);

            return ['label' => $period->translatedFormat('M Y'),
                'cents' => $groups->get($period->format('Y-m'), collect())->sum(fn ($expense) => $expense->cents())];
        });

        return $this->summarize($groups->get($month->format('Y-m'), collect())) + compact('trend');
    }
}
