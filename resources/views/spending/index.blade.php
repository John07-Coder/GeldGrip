@use('App\Support\Money')
<x-app-layout title="Uitgaven" :month="$month">
    <div class="page-heading"><div><span class="eyebrow">ELKE UITGAVE IN BEELD</span><h1>Je maandelijkse uitgaven<span class="heading-dot">.</span></h1><p>Van boodschappen tot een avond uit. Ontdek waar je geld naartoe gaat.</p></div><a class="btn btn-primary" href="{{ route('spending.create', ['month' => $month->format('Y-m')]) }}"><x-icon name="plus" />Uitgave toevoegen</a></div>
    <div class="overview-period"><span class="period-dot"></span>{{ ucfirst($month->translatedFormat('F Y')) }}<span class="period-divider">·</span>Geregistreerde losse uitgaven</div>
    <section class="stats-grid stats-four" aria-label="Uitgavenstatistieken">
        <article class="stat-card"><div class="stat-top"><span>Totale uitgaven</span><span class="stat-icon purple"><x-icon name="shopping-bag" /></span></div><strong class="stat-value">{{ Money::format($total) }}</strong><p class="stat-foot">In de geselecteerde maand</p></article>
        <article class="stat-card"><div class="stat-top"><span>Aantal uitgaven</span><span class="stat-icon green"><x-icon name="receipt-text" /></span></div><strong class="stat-value">{{ $count }}</strong><p class="stat-foot">Geregistreerde aankopen</p></article>
        <article class="stat-card"><div class="stat-top"><span>Grootste categorie</span><span class="stat-icon amber"><x-icon name="chart-pie" /></span></div><strong class="stat-value stat-category">{{ $largestCategory['label'] ?? '—' }}</strong><p class="stat-foot">{{ $largestCategory ? Money::format($largestCategory['cents']) : 'Voeg je eerste uitgave toe' }}</p></article>
        <article class="stat-card"><div class="stat-top"><span>Gemiddelde uitgave</span><span class="stat-icon green"><x-icon name="calculator" /></span></div><strong class="stat-value">{{ Money::format($average) }}</strong><p class="stat-foot">Per geregistreerde aankoop</p></article>
    </section>
    <section class="charts-grid spending-charts" aria-label="Uitgavengrafieken">
        <article class="panel"><div class="panel-heading"><div><h2>Uitgaven per categorie</h2><p>Alle uitgaven in {{ $month->translatedFormat('F Y') }}</p></div><x-icon name="chart-pie" /></div>
            @if ($total > 0)
                <div class="donut-content"><div class="donut-wrap"><canvas id="category-chart" role="img" aria-label="Verdeling van uitgaven, bedragen staan naast de grafiek."></canvas><div class="donut-label"><small>Totaal</small><strong>{{ Money::format($total) }}</strong><span>deze maand</span></div></div><div class="chart-legend">
                    @foreach ($categories->where('cents', '>', 0) as $category)<div class="legend-row"><span class="legend-name"><span class="legend-dot" style="--dot-color:{{ $category['color'] }}"></span>{{ $category['label'] }}</span><strong>{{ Money::format($category['cents']) }}</strong></div>@endforeach
                </div></div>
            @else
                <div class="chart-empty"><span class="empty-donut"><x-icon name="chart-pie" /></span><strong>Je verdeling begint met één uitgave</strong><p>Er zijn nog geen uitgaven in deze maand.</p></div>
            @endif
        </article>
        <article class="panel"><div class="panel-heading"><div><h2>De afgelopen zes maanden</h2><p>Tot en met de geselecteerde maand</p></div><x-icon name="chart-no-axes-column-increasing" /></div><div class="comparison-chart"><canvas id="trend-chart" role="img" aria-label="{{ $trend->map(fn ($period) => $period['label'].': '.Money::format($period['cents']))->implode('; ') }}"></canvas></div></article>
    </section>
    <form method="get" class="filter-bar spending-filters"><input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
        <div class="search-input"><x-icon name="search" /><label class="sr-only" for="search">Zoek uitgave</label><input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Zoek een uitgave…" maxlength="100"></div>
        <label class="sr-only" for="category">Categorie</label><select id="category" name="category"><option value="">Alle categorieën</option>@foreach (\App\Enums\ExpenseCategory::cases() as $category)<option value="{{ $category->value }}" @selected(request('category') === $category->value)>{{ $category->label() }}</option>@endforeach</select>
        <label class="sr-only" for="sort">Sorteren op</label><select id="sort" name="sort"><option value="expense_date" @selected(request('sort') !== 'amount')>Datum</option><option value="amount" @selected(request('sort') === 'amount')>Bedrag</option></select>
        <label class="sr-only" for="direction">Sorteerrichting</label><select id="direction" name="direction"><option value="desc" @selected(request('direction') !== 'asc')>Aflopend</option><option value="asc" @selected(request('direction') === 'asc')>Oplopend</option></select>
        <button class="btn btn-secondary" type="submit"><x-icon name="sliders-horizontal" />Filteren</button><a class="text-link" href="{{ route('spending.index', ['month' => $month->format('Y-m')]) }}">Wissen</a>
    </form>
    <section class="panel"><div class="panel-heading"><div><h2>Alle uitgaven</h2><p>{{ $entries->total() }} {{ $entries->total() === 1 ? 'resultaat' : 'resultaten' }} · statistieken tonen de volledige maand</p></div></div><x-spending-table :entries="$entries" :month="$month" /></section>
    <div class="pagination">{{ $entries->links() }}</div>
    <p class="calculation-note"><x-icon name="info" />Registreer hier alleen losse uitgaven die nog niet bij je vaste lasten staan.</p>
    <script id="chart-data" type="application/json">{!! json_encode(['categories' => $categories->where('cents', '>', 0)->values(), 'trend' => $trend], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</x-app-layout>
