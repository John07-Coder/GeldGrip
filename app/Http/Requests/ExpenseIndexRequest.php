<?php

namespace App\Http\Requests;

use App\Enums\ExpenseCategory;
use Illuminate\Validation\Rule;

class ExpenseIndexRequest extends MonthRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'category' => ['nullable', Rule::enum(ExpenseCategory::class)],
            'sort' => ['nullable', 'in:expense_date,amount'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);
    }
}
