<?php

namespace App\Http\Controllers;

use App\Http\Requests\FixedExpenseRequest;
use App\Http\Requests\MonthRequest;

class FixedExpenseController extends Controller
{
    public function index(MonthRequest $request)
    {
        $query = $request->user()->fixedExpenses()->orderByDesc('is_active')->orderBy('name');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->validated('search').'%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->validated('category'));
        }

        return view('entries.index', ['type' => 'expense', 'month' => $request->month(),
            'entries' => $query->paginate(15)->withQueryString()]);
    }

    public function create(MonthRequest $request)
    {
        return view('entries.form', ['type' => 'expense', 'entry' => null, 'month' => $request->month()]);
    }

    public function store(FixedExpenseRequest $request)
    {
        $request->user()->fixedExpenses()->create($request->entryData());

        return to_route($request->validated('return_to') === 'dashboard' ? 'dashboard' : 'expenses.index', ['month' => $request->validated('month')])->with('status', 'Vaste last toegevoegd. Je overzicht is bijgewerkt.');
    }

    public function edit(MonthRequest $request, string $expense)
    {
        return view('entries.form', ['type' => 'expense', 'month' => $request->month(),
            'entry' => $request->user()->fixedExpenses()->findOrFail($expense)]);
    }

    public function update(FixedExpenseRequest $request, string $expense)
    {
        $request->user()->fixedExpenses()->findOrFail($expense)->update($request->entryData());

        return to_route($request->validated('return_to') === 'dashboard' ? 'dashboard' : 'expenses.index', ['month' => $request->validated('month')])->with('status', 'Vaste last bijgewerkt.');
    }

    public function destroy(MonthRequest $request, string $expense)
    {
        $request->user()->fixedExpenses()->findOrFail($expense)->delete();

        return to_route($request->validated('return_to') === 'dashboard' ? 'dashboard' : 'expenses.index', ['month' => $request->month()->format('Y-m')])->with('status', 'Vaste last verwijderd.');
    }
}
