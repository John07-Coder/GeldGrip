<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryBudgetRequest;
use App\Http\Requests\MonthRequest;
use App\Services\BudgetOverview;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryBudgetController extends Controller
{
    public function index(MonthRequest $request, BudgetOverview $overview)
    {
        $month = $request->month();

        return view('budgets.index', ['month' => $month, ...$overview->forMonth($request->user(), $month)]);
    }

    public function create(MonthRequest $request)
    {
        return view('budgets.form', ['entry' => null, 'month' => $request->month()]);
    }

    public function store(CategoryBudgetRequest $request)
    {
        try {
            $request->user()->categoryBudgets()->create($request->entryData());
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['category' => 'Er bestaat al een budget voor deze categorie en maand.']);
        }

        return to_route('budgets.index', ['month' => $request->budgetMonth()->format('Y-m')])->with('status', 'Budget toegevoegd.');
    }

    public function edit(MonthRequest $request, string $budget)
    {
        return view('budgets.form', ['month' => $request->month(), 'entry' => $request->user()->categoryBudgets()->findOrFail($budget)]);
    }

    public function update(CategoryBudgetRequest $request, string $budget)
    {
        try {
            $request->user()->categoryBudgets()->findOrFail($budget)->update($request->entryData());
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['category' => 'Er bestaat al een budget voor deze categorie en maand.']);
        }

        return to_route('budgets.index', ['month' => $request->budgetMonth()->format('Y-m')])->with('status', 'Budget bijgewerkt.');
    }

    public function destroy(MonthRequest $request, string $budget)
    {
        $request->user()->categoryBudgets()->findOrFail($budget)->delete();

        return to_route('budgets.index', ['month' => $request->month()->format('Y-m')])->with('status', 'Budget verwijderd. Je uitgaven zijn behouden.');
    }

    public function copyPrevious(MonthRequest $request)
    {
        $month = $request->month();
        $count = DB::transaction(function () use ($request, $month) {
            $previous = $request->user()->categoryBudgets()->inMonth($month->subMonth())->get();
            $count = 0;
            foreach ($previous as $budget) {
                $copy = $request->user()->categoryBudgets()->firstOrCreate(
                    ['category' => $budget->category->value, 'year' => $month->year, 'month' => $month->month],
                    ['amount' => $budget->amount],
                );
                if ($copy->wasRecentlyCreated) {
                    $count++;
                }
            }

            return $count;
        });

        return to_route('budgets.index', ['month' => $month->format('Y-m')])->with('status',
            $count ? $count.' '.($count === 1 ? 'budget overgenomen.' : 'budgetten overgenomen.').' Bestaande budgetten zijn behouden; uitgaven zijn niet gekopieerd.' : 'Geen nieuwe budgetten om over te nemen. Bestaande budgetten en uitgaven zijn behouden.');
    }
}
