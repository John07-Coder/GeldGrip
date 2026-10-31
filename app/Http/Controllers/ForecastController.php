<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForecastRequest;
use App\Services\FinancialForecast;
use App\Support\Money;

class ForecastController extends Controller
{
    public function __invoke(ForecastRequest $request, FinancialForecast $service)
    {
        $month = $request->month();
        $baseline = $service->forMonth($request->user(), $month);
        $horizon = (int) ($request->validated('horizon') ?: 6);
        $assumptions = [];
        foreach (['income', 'fixed', 'variable'] as $field) {
            $assumptions[$field] = $request->filled($field) ? Money::cents($request->validated($field)) : null;
        }
        $projection = $service->project($baseline['months'], $horizon, $assumptions);
        $data = compact('baseline', 'horizon', 'assumptions', 'projection');

        return $request->expectsJson() ? response()->json($data) : view('forecasts', compact('month') + $data);
    }
}
