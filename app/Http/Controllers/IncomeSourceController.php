<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinancialEntryRequest;
use App\Http\Requests\MonthRequest;

class IncomeSourceController extends Controller
{
    public function index(MonthRequest $request)
    {
        return view('entries.index', ['type' => 'income', 'month' => $request->month(),
            'entries' => $request->user()->incomeSources()->orderByDesc('is_active')->orderBy('name')->paginate(15)->withQueryString()]);
    }

    public function create(MonthRequest $request)
    {
        return view('entries.form', ['type' => 'income', 'entry' => null, 'month' => $request->month()]);
    }

    public function store(FinancialEntryRequest $request)
    {
        $request->user()->incomeSources()->create($request->entryData());

        return to_route($request->validated('return_to') === 'dashboard' ? 'dashboard' : 'incomes.index', ['month' => $request->validated('month')])->with('status', 'Inkomstenbron toegevoegd. Je overzicht is bijgewerkt.');
    }

    public function edit(MonthRequest $request, string $income)
    {
        return view('entries.form', ['type' => 'income', 'month' => $request->month(),
            'entry' => $request->user()->incomeSources()->findOrFail($income)]);
    }

    public function update(FinancialEntryRequest $request, string $income)
    {
        $request->user()->incomeSources()->findOrFail($income)->update($request->entryData());

        return to_route($request->validated('return_to') === 'dashboard' ? 'dashboard' : 'incomes.index', ['month' => $request->validated('month')])->with('status', 'Inkomstenbron bijgewerkt.');
    }

    public function destroy(MonthRequest $request, string $income)
    {
        $request->user()->incomeSources()->findOrFail($income)->delete();

        return to_route($request->validated('return_to') === 'dashboard' ? 'dashboard' : 'incomes.index', ['month' => $request->month()->format('Y-m')])->with('status', 'Inkomstenbron verwijderd.');
    }
}
