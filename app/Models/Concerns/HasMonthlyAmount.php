<?php

namespace App\Models\Concerns;

use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

trait HasMonthlyAmount
{
    public function monthlyCents(): int
    {
        return Money::monthly($this->amount, $this->frequency);
    }

    public function isRelevantTo(CarbonImmutable $month): bool
    {
        return $this->is_active && $this->start_date <= $month->endOfMonth()
            && ($this->end_date === null || $this->end_date >= $month->startOfMonth());
    }

    public function scopeRelevantTo(Builder $query, CarbonImmutable $month): Builder
    {
        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', $month->endOfMonth()->toDateString())
            ->where(fn (Builder $query) => $query->whereNull('end_date')
                ->orWhereDate('end_date', '>=', $month->startOfMonth()->toDateString()));
    }
}
