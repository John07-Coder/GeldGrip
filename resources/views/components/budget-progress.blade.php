@props(['row'])
@use('App\Support\Money')
<div class="budget-progress {{ $row['tone'] }}">
    <div class="budget-progress-label"><strong>{{ $row['budget']->category->label() }}</strong><span>{{ str_replace('.', ',', (string) $row['percentage']) }}% gebruikt</span></div>
    <div class="budget-track" role="progressbar" aria-label="Budget {{ $row['budget']->category->label() }}"
        aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ min(100, $row['percentage']) }}"
        aria-valuetext="{{ str_replace('.', ',', (string) $row['percentage']) }}% gebruikt; {{ Money::format($row['spent']) }} van {{ Money::format($row['amount']) }}">
        <span style="width: {{ min(100, $row['percentage']) }}%"></span>
    </div>
</div>
