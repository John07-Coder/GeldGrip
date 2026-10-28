<?php

namespace App\Models;

use App\Enums\Frequency;
use App\Models\Concerns\HasMonthlyAmount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeSource extends Model
{
    use HasMonthlyAmount;

    protected $fillable = ['name', 'amount', 'frequency', 'start_date', 'end_date', 'is_active'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'frequency' => Frequency::class, 'start_date' => 'immutable_date',
            'end_date' => 'immutable_date', 'is_active' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
