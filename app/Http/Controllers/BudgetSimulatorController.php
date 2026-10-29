<?php

namespace App\Http\Controllers;

use App\Http\Requests\MonthRequest;
use App\Services\ExpenseOverview;
use App\Services\FinancialOverview;

class BudgetSimulatorController extends Controller
{
    public function __invoke(MonthRequest $request, FinancialOverview $overview, ExpenseOverview $expenseOverview)
    {
        $month = $request->month();
        $data = $overview->forMonth($request->user(), $month);
        $baseline = [
            'income' => $data['income'],
            'fixed' => $data['expenses']->map(fn ($entry) => [
                'name' => $entry->name, 'cents' => $entry->monthlyCents(), 'enabled' => true,
            ])->values(),
            'variable' => $expenseOverview->summarize($data['variableExpenses'])['categories']->values(),
        ];

        return $request->expectsJson() ? response()->json($baseline) : view('simulator', compact('month', 'baseline'));
    }
}
