@php($title = $entry ? 'Uitgave bewerken' : 'Uitgave toevoegen')
@php($returnTo = old('return_to', request('return_to')) === 'dashboard' ? 'dashboard' : 'spending.index')
<x-app-layout :title="$title" :month="$month">
    <a class="back-link" href="{{ route($returnTo, ['month' => $month->format('Y-m')]) }}"><x-icon name="arrow-left" />Terug naar overzicht</a>
    <div class="page-heading"><div><span class="eyebrow">ELKE UITGAVE IN BEELD</span><h1>{{ $title }}<span class="heading-dot">.</span></h1><p>Een losse aankoop, precies in de maand waarin je deze deed.</p></div></div>
    <div class="form-layout">
        <section class="panel form-panel">
            <form method="post" action="{{ $entry ? route('spending.update', $entry) : route('spending.store') }}" class="form-stack">
                @csrf @if ($entry) @method('PUT') @endif
                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                <input type="hidden" name="return_to" value="{{ $returnTo === 'dashboard' ? 'dashboard' : '' }}">
                <x-field name="name" label="Naam van uitgave" :value="$entry?->name" placeholder="Bijvoorbeeld: Albert Heijn" maxlength="100" required autofocus />
                <div class="form-grid">
                    <x-field name="amount" label="Bedrag (€)" :value="$entry?->amount" inputmode="decimal" placeholder="0,00" hint="Meer dan nul, met maximaal 2 decimalen." required />
                    <x-field name="expense_date" label="Datum" type="date" :value="$entry?->expense_date->toDateString() ?? ($month->isCurrentMonth() ? now()->toDateString() : $month->toDateString())" required />
                </div>
                <div class="field"><label for="category">Categorie</label><select id="category" name="category" required @error('category') aria-invalid="true" aria-describedby="category-error" @enderror>
                    <option value="">Kies een categorie</option>
                    @foreach (\App\Enums\ExpenseCategory::cases() as $category)
                        <option value="{{ $category->value }}" @selected(old('category', $entry?->category->value) === $category->value)>{{ $category->label() }}</option>
                    @endforeach
                </select>@error('category')<span class="field-error" id="category-error">{{ $message }}</span>@enderror</div>
                <div class="field"><label for="description">Beschrijving (optioneel)</label><textarea id="description" name="description" rows="4" maxlength="2000" @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description', $entry?->description) }}</textarea>@error('description')<span class="field-error" id="description-error">{{ $message }}</span>@enderror</div>
                <div class="form-actions"><a class="btn btn-secondary" href="{{ route($returnTo, ['month' => $month->format('Y-m')]) }}">Annuleren</a><button class="btn btn-primary" type="submit"><x-icon name="check" />{{ $entry ? 'Wijzigingen opslaan' : 'Uitgave toevoegen' }}</button></div>
            </form>
        </section>
        <aside class="form-help"><span class="insight-icon green"><x-icon name="lightbulb" /></span><h2>Losse uitgaven, helder overzicht</h2><p>Deze uitgave telt één keer mee op de gekozen datum. Er wordt geen maandgemiddelde of herhaling van gemaakt.</p><p class="help-note">Staat een betaling al bij je vaste lasten? Registreer die hier niet opnieuw, om dubbel tellen te voorkomen.</p></aside>
    </div>
</x-app-layout>
