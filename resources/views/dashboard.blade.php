@use('App\Support\Money')
<x-app-layout title="Dashboard" :month="$month">
    <div class="page-heading">
        <div><span class="eyebrow">JOUW FINANCIËLE OVERZICHT</span>
            <h1>Hallo {{ explode(' ', auth()->user()->name)[0] }}, tijd voor overzicht<span class="heading-dot">.</span>
            </h1>
            <p>Zo staat jouw maand ervoor. Alle belangrijke bedragen op één plek.</p>
        </div><a class="btn btn-primary"
            href="{{ route('spending.create', ['month' => $month->format('Y-m'), 'return_to' => 'dashboard']) }}"><x-icon
                name="plus" /> Uitgave toevoegen</a>
    </div>
    <div class="overview-period"><span class="period-dot"></span>{{ ucfirst($month->translatedFormat('F Y')) }}<span
            class="period-divider">·</span>Maandgemiddelden en geregistreerde uitgaven</div>
    @if ($incomes->isEmpty() && $expenses->isEmpty() && $variableExpenses->isEmpty())
        <section class="onboarding"><span class="onboarding-icon"><x-icon name="sparkles" /></span>
            <div>
                <h2>Jouw overzicht begint hier</h2>
                <p>Voeg je netto inkomen en vaste lasten toe. GeldGrip rekent de rest voor je uit.</p>
            </div><a
                href="{{ route('incomes.create', ['month' => $month->format('Y-m'), 'return_to' => 'dashboard']) }}"
                class="btn btn-primary">Inkomen toevoegen <x-icon name="arrow-right" /></a>
        </section>
    @elseif($incomes->isEmpty())
        <div class="alert info"><x-icon name="info" /><span>Er zijn geen actieve inkomsten in deze maand. <a
                    href="{{ route('incomes.create', ['month' => $month->format('Y-m')]) }}">Voeg een inkomstenbron
                    toe</a> voor een compleet overzicht.</span></div>
    @endif
    @if ($remaining < 0)
        <div class="alert error" role="alert"><x-icon name="triangle-alert" /><span><strong>{{ $variableCosts > 0 ? 'Je vaste lasten en uitgaven zijn hoger dan je inkomen.' : 'Je vaste lasten zijn hoger dan je inkomen.' }}</strong> Je komt {{ Money::format(abs($remaining)) }} tekort op basis van je maandgemiddelden en geregistreerde uitgaven.</span></div>
    @endif
    <section class="stats-grid stats-four" aria-label="Maandelijkse bedragen">
        <article class="stat-card">
            <div class="stat-top"><span>Netto maandinkomen</span><span class="stat-icon green"><x-icon
                        name="wallet" /></span></div><strong class="stat-value">{{ Money::format($income) }}</strong>
            <div class="stat-foot"><span class="mini-dot green"></span>{{ $incomes->count() }} actieve
                {{ $incomes->count() === 1 ? 'inkomstenbron' : 'inkomstenbronnen' }}<a
                    href="{{ route('incomes.index', ['month' => $month->format('Y-m')]) }}"
                    aria-label="Inkomsten bekijken"><x-icon name="arrow-up-right" /></a></div>
        </article>
        <article class="stat-card">
            <div class="stat-top"><span>Totale vaste lasten</span><span class="stat-icon purple"><x-icon
                        name="receipt-text" /></span></div><strong
                class="stat-value">{{ Money::format($costs) }}</strong>
            <div class="stat-foot"><span class="mini-dot purple"></span>{{ $expenses->count() }} actieve vaste
                {{ $expenses->count() === 1 ? 'last' : 'lasten' }}<a
                    href="{{ route('expenses.index', ['month' => $month->format('Y-m')]) }}"
                    aria-label="Vaste lasten bekijken"><x-icon name="arrow-up-right" /></a></div>
        </article>
        <article class="stat-card">
            <div class="stat-top"><span>Maandelijkse uitgaven</span><span class="stat-icon amber"><x-icon name="shopping-basket" /></span></div><strong class="stat-value">{{ Money::format($variableCosts) }}</strong>
            <div class="stat-foot">{{ $variableExpenses->count() }} losse uitgaven<a href="{{ route('spending.index', ['month' => $month->format('Y-m')]) }}" aria-label="Uitgaven bekijken"><x-icon name="arrow-up-right" /></a></div>
        </article>
        <article @class(['stat-card', 'stat-featured', 'negative' => $remaining < 0])>
            <div class="stat-top"><span>Resterend budget</span><span class="stat-icon"><x-icon
                        :name="$remaining < 0 ? 'trending-down' : 'trending-up'" /></span></div><strong
                class="stat-value">{{ Money::format($remaining) }}</strong>
            <div class="stat-foot"><span
                    class="percentage-badge">{{ $percentage !== null ? str_replace('.', ',', (string) $percentage) . '%' : '—' }}</span><span>{{ $percentage !== null ? 'van je netto inkomen' : 'Voeg je inkomen toe' }}</span><x-icon
                    name="sparkles" class="featured-spark" /></div>
        </article>
    </section>
    <div class="calculation-note"><x-icon name="info" /><span>Netto maandinkomen − gemiddelde vaste lasten − geregistreerde losse uitgaven in deze maand. Dit is geen banksaldo; niet-geregistreerde aankopen zijn niet meegenomen.</span></div>
    <section class="charts-grid" aria-label="Grafieken">
        <article class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Waar gaat je geld naartoe?</h2>
                    <p>Je vaste lasten per categorie</p>
                </div><span class="chart-heading-icon"><x-icon name="chart-pie" /></span>
            </div>
            @if ($costs > 0)
                <div class="donut-content">
                    <div class="donut-wrap"><canvas id="category-chart" role="img"
                            aria-label="Verdeling vaste lasten. De bedragen per categorie staan naast de grafiek."></canvas>
                        <div class="donut-label"><small>Vaste
                                lasten</small><strong>{{ Money::format($costs) }}</strong><span>per maand</span></div>
                    </div>
                    <div class="chart-legend">
                        @foreach ($categories as $category)
                            <div class="legend-row"><span class="legend-name"><span class="legend-dot"
                                        style="--dot-color: {{ $category['color'] }}"></span>{{ $category['label'] }}</span><strong>{{ Money::format($category['cents']) }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="chart-empty">
                    <div class="empty-donut"><x-icon name="chart-pie" /></div><strong>Maak je verdeling
                        zichtbaar</strong>
                    <p>Voeg een vaste last toe en ontdek waar je geld naartoe gaat.</p><a
                        href="{{ route('expenses.create', ['month' => $month->format('Y-m'), 'return_to' => 'dashboard']) }}">Voeg
                        je eerste vaste last toe <x-icon name="arrow-right" /></a>
                </div>
            @endif
        </article>
        <article class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Inkomsten & uitgaven</h2>
                    <p>Het verschil in één oogopslag</p>
                </div><span class="chart-heading-icon"><x-icon name="chart-no-axes-column-increasing" /></span>
            </div>
            <div class="comparison-chart"><canvas id="comparison-chart" role="img"
                    aria-label="Inkomsten {{ Money::format($income) }}, gemiddelde vaste lasten {{ Money::format($costs) }} en losse uitgaven {{ Money::format($variableCosts) }}."></canvas>
            </div>
            <div class="comparison-legend"><span><i class="legend-dot"
                        style="--dot-color:#10b981"></i>Inkomsten</span><span><i class="legend-dot"
                        style="--dot-color:#a78bfa"></i>Vaste lasten</span><span><i class="legend-dot" style="--dot-color:#f59e0b"></i>Uitgaven</span><span
                    class="comparison-month">{{ ucfirst($month->translatedFormat('F Y')) }}</span></div>
        </article>
    </section>
    <section class="insights-grid" aria-label="Inzichten">
        <div class="insight"><span class="insight-icon amber"><x-icon name="lightbulb" /></span>
            <div><span>JOUW GROOTSTE VASTE LAST</span>
                <p>
                    @if ($largest)
                        <strong>{{ $largest->name }}</strong> · {{ Money::format($largest->monthlyCents()) }} per
                        maand
                    @else
                        Voeg je vaste lasten toe voor persoonlijk inzicht.
                    @endif
                </p>
            </div>
        </div>
        <div class="insight"><span class="insight-icon green"><x-icon name="badge-check" /></span>
            <div><span>GOED OM TE WETEN</span>
                <p>Jaarlijkse kosten worden verdeeld over 12 maanden.</p>
            </div>
        </div>
    </section>
    <section class="dashboard-planning" aria-label="Budgetten en vooruitblik">
        <article class="panel planning-panel"><div class="planning-heading"><div><h2>Je budgetten in beeld</h2><p>Categorieën die hun limiet het dichtst naderen</p></div><a class="text-link" href="{{ route('budgets.index', ['month' => $month->format('Y-m')]) }}">Alle budgetten <x-icon name="arrow-right" /></a></div>
            @forelse ($budgetOverview['rows']->sortByDesc('percentage')->take(3) as $row)
                <x-budget-progress :row="$row" />
            @empty
                <p class="planning-empty">Geef je uitgaven een grens die bij je past.</p><a class="btn btn-secondary" href="{{ route('budgets.create', ['month' => $month->format('Y-m')]) }}"><x-icon name="plus" />Je eerste budget toevoegen</a>
            @endforelse
        </article>
        <article class="panel planning-panel"><div class="planning-heading"><div><h2>Financiële vooruitblik</h2><p>Verwacht cumulatief overschot over 6 maanden</p></div><span class="stat-icon green"><x-icon name="chart-no-axes-combined" /></span></div><strong @class(['planning-total', 'text-negative' => $forecastTotal < 0, 'text-positive' => $forecastTotal >= 0])>{{ Money::format($forecastTotal) }}</strong><p class="planning-explanation">Vanaf € 0, met bekende begin- en einddatums. {{ $forecastHistoryCount ? 'Uitgaven geschat met '.$forecastHistoryCount.' volledige '.($forecastHistoryCount === 1 ? 'maand.' : 'maanden.') : 'Nog geen uitgavenhistorie; de schatting is € 0.' }}</p><a class="text-link" href="{{ route('forecasts', ['month' => $month->format('Y-m')]) }}">Bekijk je voorspelling <x-icon name="arrow-right" /></a></article>
    </section>
    <section class="panel expenses-panel">
        <div class="panel-heading">
            <div>
                <h2>Je vaste lasten</h2>
                <p>Actieve lasten in {{ $month->translatedFormat('F Y') }}</p>
            </div><a class="text-link" href="{{ route('expenses.index', ['month' => $month->format('Y-m')]) }}">Alles
                bekijken <x-icon name="arrow-right" /></a>
        </div>
        <x-entry-table :entries="$expenses->take(5)" type="expense" :month="$month" :dashboard="true" />
        @if ($expenses->isNotEmpty())
            <div class="table-footer"><span>{{ min(5, $expenses->count()) }} van {{ $expenses->count() }} vaste
                    lasten</span><span>Gemiddeld per maand <strong>{{ Money::format($costs) }}</strong></span></div>
        @endif
    </section>
    <section class="panel expenses-panel recent-spending">
        <div class="panel-heading"><div><h2>Recente uitgaven</h2><p>Je vijf meest recente losse uitgaven in {{ $month->translatedFormat('F Y') }}</p></div><a class="text-link" href="{{ route('spending.index', ['month' => $month->format('Y-m')]) }}">Alle uitgaven bekijken <x-icon name="arrow-right" /></a></div>
        <x-spending-table :entries="$variableExpenses->take(5)" :month="$month" :dashboard="true" />
    </section>
    <script id="chart-data" type="application/json">{!! json_encode(['categories' => $categories, 'income' => $income, 'costs' => $costs, 'variableCosts' => $variableCosts], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</x-app-layout>
