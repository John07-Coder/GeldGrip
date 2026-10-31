<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;

class FinancialForecast
{
    public function forMonth(User $user, CarbonImmutable $reference): array
    {
        // Never use the reference month or the still incomplete current month as history.
        $cutoff = $reference->startOfMonth()->min(CarbonImmutable::now()->startOfMonth());
        $history = [];
        for ($index = 0; $index < 3; $index++) {
            $date = $user->expenses()->where('expense_date', '<', $cutoff->toDateString())->orderByDesc('expense_date')->value('expense_date');
            if ($date === null) {
                break;
            }
            $period = CarbonImmutable::parse($date)->startOfMonth();
            $cents = $user->expenses()->inMonth($period)->get(['amount'])->sum(fn ($expense) => $expense->cents());
            $history[] = ['month' => $period->format('Y-m'), 'label' => $period->translatedFormat('F Y'), 'cents' => $cents];
            $cutoff = $period;
        }
        $count = count($history);
        $variable = $count ? intdiv(array_sum(array_column($history, 'cents')) + intdiv($count, 2), $count) : 0;
        $incomes = $user->incomeSources()->where('is_active', true)->get();
        $fixedExpenses = $user->fixedExpenses()->where('is_active', true)->get();
        $months = [];
        for ($index = 1; $index <= 12; $index++) {
            $month = $reference->startOfMonth()->addMonths($index);
            $income = $incomes->filter(fn ($entry) => $entry->isRelevantTo($month))->sum(fn ($entry) => $entry->monthlyCents());
            $fixed = $fixedExpenses->filter(fn ($entry) => $entry->isRelevantTo($month))->sum(fn ($entry) => $entry->monthlyCents());
            $months[] = ['month' => $month->format('Y-m'), 'label' => ucfirst($month->translatedFormat('F Y')),
                'income' => $income, 'fixed' => $fixed, 'variable' => $variable];
        }

        return compact('months', 'history', 'variable') + ['reference' => $reference->format('Y-m')];
    }

    public function project(array $months, int $horizon = 6, array $assumptions = []): array
    {
        $cumulative = 0;
        $rows = [];
        foreach (array_slice($months, 0, $horizon) as $month) {
            foreach (['income', 'fixed', 'variable'] as $field) {
                $month[$field] = $assumptions[$field] ?? $month[$field];
            }
            $month['costs'] = $month['fixed'] + $month['variable'];
            $month['remaining'] = $month['income'] - $month['costs'];
            $cumulative += $month['remaining'];
            $month['cumulative'] = $cumulative;
            $rows[] = $month;
        }

        return $rows;
    }
}
