@use('App\Support\Money')
@php($first = $projection[0])
@php($last = $projection[count($projection) - 1])
<x-app-layout title="Voorspellingen" :month="$month">
    <div class="page-heading"><div><span class="eyebrow">EEN BLIK VOORUIT</span><h1>Ruimte voor je toekomst<span class="heading-dot">.</span></h1><p>Ontdek wat je de komende maanden naar verwachting overhoudt.</p></div><span class="soft-badge">Projectie vanaf € 0</span></div>
    <form method="get" action="{{ route('forecasts') }}" id="forecast-form">
        <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
        <div class="forecast-toolbar"><div class="period-tabs" role="group" aria-label="Voorspellingsperiode">
            @foreach ([3, 6, 12] as $period)<button type="submit" name="horizon" value="{{ $period }}" data-forecast-period="{{ $period }}" @class(['period-tab', 'selected' => $horizon === $period]) aria-pressed="{{ $horizon === $period ? 'true' : 'false' }}">{{ $period }} maanden</button>@endforeach
        </div><p id="forecast-range">{{ $first['label'] }} – {{ $last['label'] }}</p></div>
        <section class="stats-grid stats-four" aria-label="Voorspellingsbedragen">
            <article class="stat-card"><div class="stat-top"><span>Verwachte maandelijkse inkomsten</span><span class="stat-icon green"><x-icon name="wallet" /></span></div><strong class="stat-value" id="forecast-income">{{ Money::format($first['income']) }}</strong><p class="stat-foot" data-first-month>In {{ mb_strtolower($first['label']) }}</p></article>
            <article class="stat-card"><div class="stat-top"><span>Verwachte maandelijkse kosten</span><span class="stat-icon purple"><x-icon name="receipt-text" /></span></div><strong class="stat-value" id="forecast-costs">{{ Money::format($first['costs']) }}</strong><p class="stat-foot">Vaste lasten + geschatte uitgaven</p></article>
            <article @class(['stat-card', 'stat-featured', 'negative' => $first['remaining'] < 0]) id="forecast-monthly-card"><div class="stat-top"><span>Verwacht maandelijks overschot</span><span class="stat-icon"><x-icon name="trending-up" /></span></div><strong class="stat-value" id="forecast-remaining">{{ Money::format($first['remaining']) }}</strong><p class="stat-foot" data-first-month>In {{ mb_strtolower($first['label']) }}</p></article>
            <article @class(['stat-card', 'stat-featured', 'negative' => $last['cumulative'] < 0]) id="forecast-total-card"><div class="stat-top"><span id="forecast-total-label">Cumulatief na {{ $horizon }} maanden</span><span class="stat-icon"><x-icon name="chart-no-axes-combined" /></span></div><strong class="stat-value" id="forecast-total">{{ Money::format($last['cumulative']) }}</strong><p class="stat-foot">Verwacht overschot vanaf € 0</p></article>
        </section>
        <p class="calculation-note"><x-icon name="info" /><span>De maandkaarten tonen de eerste voorspelde maand. De tabel rekent iedere maand afzonderlijk uit, met bekende begin- en einddatums.</span></p>
    <section class="panel forecast-chart-panel"><div class="panel-heading"><div><h2>Zo kan je overschot groeien</h2><p>Cumulatief verwacht resultaat, beginnend bij € 0</p></div><x-icon name="chart-no-axes-combined" /></div><div class="forecast-chart"><canvas id="forecast-chart" role="img" aria-label="Cumulatief verwacht overschot per maand. De bedragen staan in de tabel hieronder."></canvas></div></section>
        <section class="panel forecast-assumptions"><div><h2>Voorspelling aanpassen</h2><p>Alleen voor deze projectie. Laat een veld leeg om de standaardwaarden te volgen.</p></div>
            <div class="forecast-fields">
                @foreach (['income' => ['Verwacht netto maandinkomen (€)', $baseline['months'][0]['income']], 'fixed' => ['Verwachte vaste lasten (€)', $baseline['months'][0]['fixed']], 'variable' => ['Verwachte variabele uitgaven (€)', $baseline['variable']]] as $field => [$label, $default])
                    <div class="field"><label for="forecast-{{ $field }}-input">{{ $label }}</label><input id="forecast-{{ $field }}-input" name="{{ $field }}" data-forecast-input="{{ $field }}" inputmode="decimal" value="{{ $assumptions[$field] !== null ? number_format($assumptions[$field] / 100, 2, '.', '') : '' }}" placeholder="{{ number_format($default / 100, 2, ',', '') }}" aria-describedby="forecast-{{ $field }}-help"><small id="forecast-{{ $field }}-help">Standaard: {{ Money::format($default) }}{{ $field !== 'variable' ? ' in de eerste maand' : ' per maand' }}.</small></div>
                @endforeach
            </div>
            <p class="assumptions-note">Een ingevuld bedrag geldt voor alle voorspelde maanden en vervangt daar tijdelijk de geregistreerde bedragen. Standaard volgen inkomsten en vaste lasten hun eigen begin- en einddatums.</p>
            <p class="alert error" id="forecast-error" role="alert" hidden>Gebruik nul of meer en maximaal twee decimalen. De laatste geldige voorspelling blijft staan.</p>
            <div class="forecast-reset-row"><button type="button" class="btn btn-secondary" id="reset-forecast"><x-icon name="rotate-ccw" />Standaardwaarden herstellen</button><span id="forecast-status" role="status" aria-live="polite"></span></div>
            <noscript><button class="btn btn-primary" type="submit" name="horizon" value="{{ $horizon }}">Voorspelling bijwerken</button><p><a href="{{ route('forecasts', ['month' => $month->format('Y-m'), 'horizon' => $horizon]) }}">Standaardwaarden herstellen</a></p></noscript>
        </section>
    </form>
    <div class="alert info forecast-history"><x-icon name="info" /><div id="forecast-history">
        @if (count($baseline['history']))
            <strong>Schatting op basis van {{ count($baseline['history']) }} volledige {{ count($baseline['history']) === 1 ? 'maand' : 'maanden' }} met uitgaven.</strong> {{ collect($baseline['history'])->pluck('label')->reverse()->implode(', ') }}. Maanden zonder geregistreerde uitgaven zijn niet als nulmaand aangenomen.
        @else
            <strong>Nog geen bruikbare uitgavenhistorie.</strong> De standaard schatting is € 0. Vul hierboven zelf je verwachte variabele maanduitgaven in.
        @endif
    </div></div>

    <section class="panel forecast-table-panel"><div class="panel-heading"><div><h2>Maand voor maand</h2><p>Een schatting op basis van je gegevens en tijdelijke aannames.</p></div></div>
        <table class="entries-table forecast-table"><thead><tr><th scope="col">Maand</th><th scope="col">Verwachte inkomsten</th><th scope="col">Verwachte vaste lasten</th><th scope="col">Geschatte variabele uitgaven</th><th scope="col">Verwacht maandresultaat</th><th scope="col">Cumulatief resultaat</th></tr></thead>
            <tbody id="forecast-rows">@foreach ($projection as $row)<tr><th scope="row">{{ $row['label'] }}</th>@foreach (['income' => 'Verwachte inkomsten', 'fixed' => 'Verwachte vaste lasten', 'variable' => 'Geschatte variabele uitgaven', 'remaining' => 'Verwacht maandresultaat', 'cumulative' => 'Cumulatief resultaat'] as $key => $label)<td data-label="{{ $label }}" @class(['amount-cell', 'text-negative' => $row[$key] < 0])>{{ Money::format($row[$key]) }}</td>@endforeach</tr>@endforeach</tbody>
        </table>
    </section>
    <p class="calculation-note"><x-icon name="info" /><span>Deze voorspelling is een schatting op basis van je geregistreerde inkomsten, vaste lasten en verwachte uitgaven. Werkelijke bedragen kunnen afwijken. Het cumulatieve overschot is geen daadwerkelijk banksaldo. Niet-geregistreerde uitgaven vallen buiten deze voorspelling.</span></p>
    <script id="forecast-data" type="application/json">{!! json_encode(compact('baseline', 'horizon', 'assumptions'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</x-app-layout>
