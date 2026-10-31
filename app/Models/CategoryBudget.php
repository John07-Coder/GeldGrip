<?php

namespace App\Models;

use App\Enums\ExpenseCategory;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryBudget extends Model
{
    protected $fillable = ['category', 'amount', 'month', 'year'];

    protected function casts(): array
    {
        return ['category' => ExpenseCategory::class, 'amount' => 'decimal:2', 'month' => 'integer', 'year' => 'integer'];
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
        return $query->where('year', $month->year)->where('month', $month->month);
    }
}
