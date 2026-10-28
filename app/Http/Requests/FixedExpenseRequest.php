<?php

namespace App\Http\Requests;

use App\Enums\Category;
use Illuminate\Validation\Rule;

class FixedExpenseRequest extends FinancialEntryRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'category' => ['required', Rule::enum(Category::class)],
            'payment_day' => ['nullable', 'integer', 'between:1,31'],
        ]);
    }
}
