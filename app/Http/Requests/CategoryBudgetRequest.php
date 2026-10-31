<?php

namespace App\Http\Requests;

use App\Enums\ExpenseCategory;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->amount)) {
            $this->merge(['amount' => str_replace(',', '.', trim($this->amount))]);
        }
    }

    public function rules(): array
    {
        $budget = $this->route('budget') ? $this->user()->categoryBudgets()->findOrFail($this->route('budget')) : null;
        $period = is_string($this->budget_month) ? $this->budget_month : '';

        return [
            'category' => ['bail', 'required', Rule::enum(ExpenseCategory::class),
                Rule::unique('category_budgets')->where('user_id', $this->user()->id)
                    ->where('year', (int) substr($period, 0, 4))->where('month', (int) substr($period, 5, 2))->ignore($budget?->id)],
            'amount' => ['bail', 'required', 'numeric', 'gt:0', 'max:99999999.99', 'regex:/^\d{1,8}(?:\.\d{1,2})?$/D'],
            'budget_month' => ['required', 'date_format:Y-m', 'regex:/^[1-9]\d{3}-(0[1-9]|1[0-2])$/D'],
        ];
    }

    public function messages(): array
    {
        return ['category.unique' => 'Je hebt al een budget voor deze categorie in deze maand. Bewerk het bestaande budget.'];
    }

    public function budgetMonth(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m', $this->validated('budget_month'));
    }

    public function entryData(): array
    {
        return $this->safe()->only(['category', 'amount']) + ['month' => $this->budgetMonth()->month, 'year' => $this->budgetMonth()->year];
    }
}
