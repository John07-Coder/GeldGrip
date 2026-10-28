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

    public function scopeRelevantTo(Builder $query, CarbonImmutable $month): Builder
    {
        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', $month->endOfMonth()->toDateString())
            ->where(fn (Builder $query) => $query->whereNull('end_date')
                ->orWhereDate('end_date', '>=', $month->startOfMonth()->toDateString()));
    }
}
