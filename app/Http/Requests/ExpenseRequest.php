<?php

namespace App\Http\Requests;

use App\Enums\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999999.99', 'regex:/^\d{1,8}(?:\.\d{1,2})?$/D'],
            'category' => ['required', Rule::enum(ExpenseCategory::class)],
            'expense_date' => ['required', 'date_format:Y-m-d'],
            'description' => ['nullable', 'string', 'max:2000'],
            'month' => ['nullable', 'date_format:Y-m'],
            'return_to' => ['nullable', 'in:dashboard'],
        ];
    }

    public function entryData(): array
    {
        return $this->safe()->except(['month', 'return_to']);
    }
}
