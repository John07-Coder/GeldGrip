@use('App\Support\Money')
<x-app-layout title="Budgetten" :month="$month">
    <div class="page-heading">
        <div><span class="eyebrow">RUIMTE VOOR WAT JIJ BELANGRIJK VINDT</span><h1>Mijn budgetten<span class="heading-dot">.</span></h1><p>Geef elke categorie een grens en houd zicht op wat nog kan.</p></div>
        <a class="btn btn-primary" href="{{ route('budgets.create', ['month' => $month->format('Y-m')]) }}"><x-icon name="plus" />Budget toevoegen</a>
    </div>
    <div class="overview-period"><span class="period-dot"></span>{{ ucfirst($month->translatedFormat('F Y')) }}<span class="period-divider">·</span>Budgetten voor losse uitgaven</div>
    <section class="stats-grid stats-four" aria-label="Budgetstatistieken">
        <article class="stat-card"><div class="stat-top"><span>Totaal budget</span><span class="stat-icon green"><x-icon name="target" /></span></div><strong class="stat-value">{{ Money::format($total) }}</strong><p class="stat-foot">{{ $rows->count() }} {{ $rows->count() === 1 ? 'categorie' : 'categorieën' }} met een budget</p></article>
        <article class="stat-card"><div class="stat-top"><span>Totaal uitgegeven</span><span class="stat-icon purple"><x-icon name="shopping-basket" /></span></div><strong class="stat-value">{{ Money::format($spent) }}</strong><p class="stat-foot">Binnen categorieën met een budget</p></article>
        <article @class(['stat-card', 'stat-featured', 'negative' => $remaining < 0])><div class="stat-top"><span>Nog beschikbaar</span><span class="stat-icon"><x-icon name="wallet" /></span></div><strong class="stat-value">{{ Money::format($remaining) }}</strong><p class="stat-foot">Voor de ingestelde budgetcategorieën</p></article>
        <article class="stat-card"><div class="stat-top"><span>Overschreden budgetten</span><span class="stat-icon amber"><x-icon name="triangle-alert" /></span></div><strong class="stat-value">{{ $exceeded }}</strong><p class="stat-foot">Categorieën boven hun maandbudget</p></article>
    </section>
    <p class="calculation-note"><x-icon name="info" /><span>Uitgaven zonder categoriebudget tellen niet mee in ‘Nog beschikbaar’. Deze maand is dat {{ Money::format($unbudgeted) }}. Vaste lasten vallen buiten deze budgetten.</span></p>
    @foreach ($rows->whereNotNull('warning') as $row)
        <div @class(['alert', 'error' => $row['remaining'] <= 0, 'budget-warning' => $row['remaining'] > 0]) role="status"><x-icon name="triangle-alert" /><span>{{ $row['warning'] }}</span></div>
    @endforeach
    @if ($rows->isEmpty())
        <section class="panel empty-state">
            <span class="empty-icon"><x-icon name="target" /></span><h2>Een nieuwe maand, jouw eigen grenzen</h2><p>Stel je eerste budget in of neem de bedragen van {{ $month->subMonth()->translatedFormat('F Y') }} over. Uitgaven worden niet gekopieerd.</p>
            <div class="budget-empty-actions"><a class="btn btn-primary" href="{{ route('budgets.create', ['month' => $month->format('Y-m')]) }}"><x-icon name="plus" />Budget toevoegen</a>
                <form method="post" action="{{ route('budgets.copy') }}">@csrf<input type="hidden" name="month" value="{{ $month->format('Y-m') }}"><button type="submit" class="btn btn-secondary"><x-icon name="copy" />Budgetten van vorige maand overnemen</button></form>
            </div>
        </section>
    @else
        <section class="budget-grid" aria-label="Budgetten per categorie">
            @foreach ($rows as $row)
                <article class="panel budget-card">
                    <div class="budget-card-top"><span class="entry-category-icon" style="--category-color: {{ $row['budget']->category->color() }}"><x-icon :name="$row['budget']->category->icon()" /></span><span class="soft-badge">Maandbudget</span>
                        <div class="row-actions"><a class="icon-button" href="{{ route('budgets.edit', [$row['budget'], 'month' => $month->format('Y-m')]) }}" aria-label="Budget {{ $row['budget']->category->label() }} bewerken"><x-icon name="pencil" /></a>
                            <form method="post" action="{{ route('budgets.destroy', [$row['budget'], 'month' => $month->format('Y-m')]) }}" data-confirm="Wil je dit budget verwijderen? De geregistreerde uitgaven blijven behouden.">@csrf @method('DELETE')<button class="icon-button delete-button" type="submit" aria-label="Budget {{ $row['budget']->category->label() }} verwijderen"><x-icon name="trash-2" /></button></form>
                        </div>
                    </div>
                    <x-budget-progress :row="$row" />
                    <dl class="budget-amounts"><div><dt>Maandbudget</dt><dd>{{ Money::format($row['amount']) }}</dd></div><div><dt>Uitgegeven</dt><dd>{{ Money::format($row['spent']) }}</dd></div><div><dt>Nog beschikbaar</dt><dd @class(['text-negative' => $row['remaining'] < 0, 'text-positive' => $row['remaining'] >= 0])>{{ Money::format($row['remaining']) }}</dd></div></dl>
                    @if ($row['remaining'] < 0)<p class="budget-overrun">Budget overschreden met {{ Money::format(-$row['remaining']) }}</p>@endif
                </article>
            @endforeach
        </section>
    @endif
</x-app-layout>
