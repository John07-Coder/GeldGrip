@php($isExpense = $type === 'expense')
@php($prefix = $isExpense ? 'expenses' : 'incomes')
@php($title = ($isExpense ? 'Vaste last' : 'Inkomstenbron') . ' ' . ($entry ? 'bewerken' : 'toevoegen'))
@php($returnTo = old('return_to', request('return_to')) === 'dashboard' ? 'dashboard' : '')
<x-app-layout :title="$title" :month="$month">
    <a class="back-link" href="{{ route($returnTo ?: $prefix . '.index', ['month' => $month->format('Y-m')]) }}"><x-icon
            name="arrow-left" />Terug naar {{ $returnTo ? 'dashboard' : ($isExpense ? 'vaste lasten' : 'inkomsten') }}</a>
    <div class="page-heading">
        <div><span class="eyebrow">{{ $entry ? 'HOUD JE OVERZICHT ACTUEEL' : 'WEER EEN STAPJE MEER OVERZICHT' }}</span>
            <h1>{{ $title }}<span class="heading-dot">.</span></h1>
            <p>{{ $isExpense ? 'Vul de gegevens van je terugkerende uitgave in.' : 'Vul het netto bedrag in dat je daadwerkelijk ontvangt.' }}
            </p>
        </div>
    </div>
    <div class="form-layout">
        <section class="panel form-panel">
            <form method="post" action="{{ $entry ? route($prefix . '.update', $entry) : route($prefix . '.store') }}"
                class="form-stack">@csrf @if ($entry)
                    @method('PUT')
                @endif
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                <div class="form-section-heading"><span class="stat-icon green"><x-icon :name="$isExpense ? 'receipt-text' : 'wallet'" /></span>
                    <div>
                        <h2>De belangrijkste gegevens</h2>
                        <p>Velden zonder ‘optioneel’ zijn verplicht.</p>
                    </div>
                </div>
                <x-field name="name" :label="$isExpense ? 'Naam vaste last' : 'Naam inkomstenbron'" :value="$entry?->name" :placeholder="$isExpense ? 'Bijvoorbeeld: Huur appartement' : 'Bijvoorbeeld: Salaris'" maxlength="100" required
                    autofocus />
                <div class="form-grid"><x-field name="amount" :label="$isExpense ? 'Bedrag per betaling (€)' : 'Netto bedrag per betaling (€)'" :value="$entry?->amount" placeholder="0,00"
                        inputmode="decimal" required hint="Gebruik een komma of punt, met maximaal 2 decimalen." />
                    <div class="field"><label for="frequency">Betalingsfrequentie</label><select name="frequency"
                            id="frequency" required>
                            @foreach (\App\Enums\Frequency::cases() as $frequency)
                                <option value="{{ $frequency->value }}" @selected(old('frequency', $entry?->frequency->value ?? 'monthly') === $frequency->value)>
                                    {{ $frequency->label() }}</option>
                            @endforeach
                        </select>
                        @error('frequency')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                @if ($isExpense)
                    <div class="form-grid">
                        <div class="field"><label for="category">Categorie</label><select name="category"
                                id="category" required>
                                <option value="">Kies een categorie</option>
                                @foreach (\App\Enums\Category::cases() as $category)
                                    <option value="{{ $category->value }}" @selected(old('category', $entry?->category->value) === $category->value)>
                                        {{ $category->label() }}</option>
                                @endforeach
                            </select>
                            @error('category')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div><x-field name="payment_day" label="Betaaldag (optioneel)" type="number" :value="$entry?->payment_day"
                            min="1" max="31" hint="Dag van de maand (1 t/m 31)." />
                    </div>
                @endif
                <div class="form-divider"></div>
                <h2>Periode en status</h2>
                <div class="form-grid"><x-field name="start_date" label="Startdatum" type="date" :value="$entry?->start_date->format('Y-m-d') ?? $month->format('Y-m-d')"
                        required /><x-field name="end_date" label="Einddatum (optioneel)" type="date"
                        :value="$entry?->end_date?->format('Y-m-d')" hint="Laat leeg als deze post doorloopt." /></div>
                <div class="toggle-field">
                    <div><strong>Actief meenemen</strong>
                        <p>Tel deze post mee in je maandoverzicht.</p>
                    </div><input type="hidden" name="is_active" value="0"><label class="switch"><input
                            type="checkbox" name="is_active" value="1" @checked(old('is_active', $entry?->is_active ?? true))
                            aria-label="Actief meenemen"><span></span></label>
                </div>
                <div class="form-actions"><a class="btn btn-secondary"
                        href="{{ route($returnTo ?: $prefix . '.index', ['month' => $month->format('Y-m')]) }}">Annuleren</a><button
                        class="btn btn-primary" type="submit"><x-icon
                            name="check" />{{ $entry ? 'Wijzigingen opslaan' : ($isExpense ? 'Vaste last toevoegen' : 'Inkomstenbron toevoegen') }}</button>
                </div>
            </form>
        </section>
        <aside class="form-help"><span class="insight-icon green"><x-icon name="lightbulb" /></span>
            <h2>Zo rekent GeldGrip</h2>
            <p>Je overzicht gebruikt gemiddelde maandbedragen. Zo kun je alle posten eerlijk vergelijken.</p>
            <dl>
                <div>
                    <dt>Wekelijks</dt>
                    <dd>bedrag × 52 ÷ 12</dd>
                </div>
                <div>
                    <dt>Maandelijks</dt>
                    <dd>bedrag</dd>
                </div>
                <div>
                    <dt>Per kwartaal</dt>
                    <dd>bedrag ÷ 3</dd>
                </div>
                <div>
                    <dt>Jaarlijks</dt>
                    <dd>bedrag ÷ 12</dd>
                </div>
            </dl>
            <p class="help-note"><x-icon name="info" />Een maandgemiddelde is geen daadwerkelijke maandelijkse
                afschrijving. Een deels actieve maand telt volledig mee.</p>
        </aside>
    </div>
</x-app-layout>
