<?php

namespace App\Http\Controllers;

use App\Http\Requests\MonthRequest;
use App\Services\BudgetOverview;
use App\Services\FinancialForecast;
use App\Services\FinancialOverview;

class DashboardController extends Controller
{
    public function __invoke(MonthRequest $request, FinancialOverview $overview, BudgetOverview $budgets, FinancialForecast $forecasts)
    {
        $month = $request->month();

        $financial = $overview->forMonth($request->user(), $month);
        $budgetOverview = $budgets->forMonth($request->user(), $month, $financial['variableExpenses']);
        $forecastBaseline = $forecasts->forMonth($request->user(), $month);
        $forecastRows = $forecasts->project($forecastBaseline['months']);

        return view('dashboard', ['month' => $month, ...$financial, 'budgetOverview' => $budgetOverview,
            'forecastTotal' => $forecastRows[5]['cumulative'], 'forecastHistoryCount' => count($forecastBaseline['history'])]);
    }
}
