<?php

namespace App\Http\Controllers;

use App\Http\Requests\MonthRequest;
use App\Services\FinancialOverview;

class DashboardController extends Controller
{
    public function __invoke(MonthRequest $request, FinancialOverview $overview)
    {
        $month = $request->month();

        return view('dashboard', ['month' => $month, ...$overview->forMonth($request->user(), $month)]);
    }
}
