<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseIndexRequest;
use App\Http\Requests\ExpenseRequest;
use App\Services\ExpenseOverview;

class ExpenseController extends Controller
{
    public function index(ExpenseIndexRequest $request, ExpenseOverview $overview)
    {
        $month = $request->month();
        $query = $request->user()->expenses()->inMonth($month);
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->validated('search').'%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->validated('category'));
        }
        $query->orderBy($request->validated('sort') ?: 'expense_date', $request->validated('direction') ?: 'desc')->orderByDesc('id');

        return view('spending.index', ['month' => $month, 'entries' => $query->paginate(15)->withQueryString(),
            ...$overview->forMonth($request->user(), $month)]);
    }

    public function create(ExpenseIndexRequest $request)
    {
        return view('spending.form', ['entry' => null, 'month' => $request->month()]);
    }

    public function store(ExpenseRequest $request)
    {
        $request->user()->expenses()->create($request->entryData());

        return $this->saved($request, 'Uitgave toegevoegd. Je overzicht is bijgewerkt.');
    }

    public function edit(ExpenseIndexRequest $request, string $spending)
    {
        return view('spending.form', ['month' => $request->month(),
            'entry' => $request->user()->expenses()->findOrFail($spending)]);
    }

    public function update(ExpenseRequest $request, string $spending)
    {
        $request->user()->expenses()->findOrFail($spending)->update($request->entryData());

        return $this->saved($request, 'Uitgave bijgewerkt.');
    }

    public function destroy(ExpenseIndexRequest $request, string $spending)
    {
        $request->user()->expenses()->findOrFail($spending)->delete();

        return to_route($request->validated('return_to') === 'dashboard' ? 'dashboard' : 'spending.index',
            ['month' => $request->month()->format('Y-m')])->with('status', 'Uitgave verwijderd.');
    }

    private function saved(ExpenseRequest $request, string $message)
    {
        // Show the month of the saved expense, including when its date has changed.
        return to_route($request->validated('return_to') === 'dashboard' ? 'dashboard' : 'spending.index',
            ['month' => substr($request->validated('expense_date'), 0, 7)])->with('status', $message);
    }
}
