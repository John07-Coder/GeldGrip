<?php

namespace App\Http\Requests;

use App\Enums\Category;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MonthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['month' => ['nullable', 'date_format:Y-m'], 'return_to' => ['nullable', 'in:dashboard'], 'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::enum(Category::class)]];
    }

    public function month(): CarbonImmutable
    {
        return $this->filled('month')
            ? CarbonImmutable::createFromFormat('!Y-m', $this->validated('month'))
            : CarbonImmutable::now()->startOfMonth();
    }
}
