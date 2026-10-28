<?php

namespace App\Http\Requests;

use App\Enums\Frequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinancialEntryRequest extends FormRequest
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
            'frequency' => ['required', Rule::enum(Frequency::class)],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'is_active' => ['required', 'boolean'],
            'month' => ['nullable', 'date_format:Y-m'],
            'return_to' => ['nullable', 'in:dashboard'],
        ];
    }

    public function entryData(): array
    {
        return $this->safe()->except(['month', 'return_to']);
    }
}
