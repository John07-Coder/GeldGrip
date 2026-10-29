<?php

namespace App\Models;

use App\Enums\ExpenseCategory;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = ['name', 'amount', 'category', 'expense_date', 'description'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'category' => ExpenseCategory::class, 'expense_date' => 'immutable_date'];
    }

    public function cents(): int
    {
        return Money::cents($this->amount);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeInMonth(Builder $query, CarbonImmutable $month): Builder
    {
        return $query->where('expense_date', '>=', $month->startOfMonth()->toDateString())
            ->where('expense_date', '<', $month->startOfMonth()->addMonth()->toDateString());
    }
}
