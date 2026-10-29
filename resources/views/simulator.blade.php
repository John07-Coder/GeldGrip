<x-app-layout title="Budgetsimulator" :month="$month">
    <div class="page-heading"><div><span class="eyebrow">KIJK MET VERTROUWEN VOORUIT</span><h1>Wat als…<span class="heading-dot">?</span></h1><p>Ontdek wat een ander inkomen of nieuwe kosten betekenen voor jouw maand.</p></div><button type="button" class="btn btn-secondary" id="reset-scenario"><x-icon name="rotate-ccw" />Scenario resetten</button></div>
    <div class="alert info"><x-icon name="flask-conical" /><span><strong>Je bent aan het simuleren.</strong> Je werkelijke gegevens veranderen niet. Je begint met {{ $month->translatedFormat('F Y') }}; het scenario wordt niet opgeslagen.</span></div>
    <noscript><div class="alert error">Schakel JavaScript in om de live budgetsimulator te gebruiken.</div></noscript>
    <div class="simulator-grid" id="simulator">
        <div class="scenario-controls">
            <section class="panel form-panel"><div class="sim-section-title"><span class="stat-icon green"><x-icon name="wallet" /></span><div><h2>Inkomen simuleren</h2><p>Netto maandinkomen</p></div></div>
                <div class="field"><label for="scenario-income">Gesimuleerd inkomen (€)</label><input id="scenario-income" inputmode="decimal" aria-describedby="income-help" required><small id="income-help">Huidig: <span id="current-income"></span> · Verschil: <span id="income-difference"></span></small></div>
                <label class="sr-only" for="income-slider">Inkomen aanpassen met schuifregelaar</label><input type="range" id="income-slider" min="0" step="0.01" value="0">
            </section>
            <section class="panel form-panel"><div class="sim-section-title"><span class="stat-icon purple"><x-icon name="receipt-text" /></span><div><h2>Vaste lasten simuleren</h2><p>Alle bedragen zijn maandgemiddelden.</p></div></div>
                <div id="fixed-scenario-rows" class="scenario-rows"></div>
                <form id="add-scenario-cost" class="hypothetical-form"><h3>Een hypothetische vaste last</h3><div class="form-grid"><div class="field"><label for="hypothetical-name">Naam</label><input id="hypothetical-name" maxlength="100" placeholder="Bijv. autoverzekering" required></div><div class="field"><label for="hypothetical-amount">Per maand (€)</label><input id="hypothetical-amount" inputmode="decimal" placeholder="110,00" required></div></div><p id="hypothetical-error" class="field-error" role="alert" hidden></p><button class="btn btn-secondary" type="submit"><x-icon name="plus" />Tijdelijk toevoegen</button></form>
            </section>
            <section class="panel form-panel"><div class="sim-section-title"><span class="stat-icon amber"><x-icon name="shopping-basket" /></span><div><h2>Variabele uitgaven simuleren</h2><p>Geregistreerde maandtotalen per categorie; vul je verwachting in.</p></div></div><div id="variable-scenario-rows" class="scenario-rows"></div></section>
        </div>
        <div class="scenario-results">
            <section class="panel form-panel"><h2>Jouw live vergelijking</h2><p class="section-description">Alle bedragen per maand. Wijzig links een bedrag om het effect te zien.</p><p class="alert error" id="scenario-error" role="alert" hidden>Controleer de gemarkeerde bedragen. Gebruik nul of meer en maximaal twee decimalen. De laatste geldige vergelijking blijft staan.</p>
                <div class="scenario-comparison"><div><h3>Huidige situatie</h3><dl id="current-summary" class="financial-summary"></dl></div><div class="simulated-summary"><h3>Gesimuleerde situatie</h3><dl id="simulated-summary" class="financial-summary"></dl></div></div>
                <div class="scenario-delta" role="status" aria-live="polite" aria-atomic="true"><span>Verschil in resterend budget</span><strong id="scenario-difference"></strong><small id="scenario-verdict"></small></div>
                <div class="simulator-chart"><canvas id="simulator-chart" role="img" aria-label="Vergelijking van huidige en gesimuleerde bedragen. Alle bedragen staan hierboven."></canvas></div>
            </section>
            <p class="calculation-note"><x-icon name="info" />Vaste lasten tellen eenmaal mee als maandgemiddelde. Registreer dezelfde betaling niet nogmaals als losse uitgave. Reset haalt het uitgangspunt opnieuw uit de database op.</p>
        </div>
    </div>
    <script id="simulator-data" type="application/json">{!! json_encode($baseline, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</x-app-layout>
