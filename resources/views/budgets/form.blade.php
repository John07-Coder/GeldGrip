@php($title = $entry ? 'Budget bewerken' : 'Budget toevoegen')
<x-app-layout :title="$title" :month="$month">
    <a class="back-link" href="{{ route('budgets.index', ['month' => $month->format('Y-m')]) }}"><x-icon name="arrow-left" />Terug naar budgetten</a>
    <div class="page-heading"><div><span class="eyebrow">JOUW MAAND, JOUW KEUZES</span><h1>{{ $title }}<span class="heading-dot">.</span></h1><p>Kies hoeveel ruimte je deze maand aan een categorie wilt geven.</p></div></div>
    <div class="form-layout">
        <section class="panel form-panel">
            <form class="form-stack" method="post" action="{{ $entry ? route('budgets.update', $entry) : route('budgets.store') }}">
                @csrf @if ($entry) @method('PUT') @endif
                <div class="field"><label for="category">Categorie</label><select id="category" name="category" required @error('category') aria-invalid="true" aria-describedby="category-error" @enderror>
                    <option value="">Kies een categorie</option>
                    @foreach (\App\Enums\ExpenseCategory::cases() as $category)<option value="{{ $category->value }}" @selected(old('category', $entry?->category->value) === $category->value)>{{ $category->label() }}</option>@endforeach
                </select>@error('category')<span class="field-error" id="category-error">{{ $message }}</span>@enderror</div>
                <x-field name="amount" label="Maandbudget (€)" :value="$entry?->amount" inputmode="decimal" required placeholder="250,00" hint="Een positief bedrag met maximaal twee decimalen." />
                <x-field name="budget_month" label="Maand en jaar" type="month" :value="$entry ? sprintf('%04d-%02d', $entry->year, $entry->month) : $month->format('Y-m')" min="1000-01" max="9999-12" required />
                <div class="form-actions"><a class="btn btn-secondary" href="{{ route('budgets.index', ['month' => $month->format('Y-m')]) }}">Annuleren</a><button class="btn btn-primary" type="submit"><x-icon name="check" />Budget opslaan</button></div>
            </form>
        </section>
        <aside class="form-help"><span class="insight-icon green"><x-icon name="target" /></span><h2>Een grens die voor jou werkt</h2><p>Je bestaande losse uitgaven in deze categorie tellen automatisch mee. Het budget maakt geen extra uitgaven aan.</p><p class="help-note">Per categorie kun je één budget per maand instellen. Budgetten uit eerdere maanden blijven bewaard.</p></aside>
    </div>
</x-app-layout>
