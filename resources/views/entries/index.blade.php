@php($isExpense = $type === 'expense')
@php($prefix = $isExpense ? 'expenses' : 'incomes')
<x-app-layout :title="$isExpense ? 'Vaste lasten' : 'Inkomsten'" :month="$month">
    <div class="page-heading">
        <div><span class="eyebrow">{{ $isExpense ? 'ALLES OP EEN RIJ' : 'JOUW FINANCIËLE BASIS' }}</span>
            <h1>{{ $isExpense ? 'Je vaste lasten' : 'Je inkomsten' }}<span class="heading-dot">.</span></h1>
            <p>{{ $isExpense ? 'Een helder overzicht van je terugkerende uitgaven.' : 'Beheer alle bronnen van je netto inkomen op één plek.' }}
            </p>
        </div><a href="{{ route($prefix . '.create', ['month' => $month->format('Y-m')]) }}" class="btn btn-primary"><x-icon
                name="plus" />{{ $isExpense ? 'Vaste last toevoegen' : 'Inkomen toevoegen' }}</a>
    </div>
    @if ($isExpense)
        <form method="get" class="filter-bar"><input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
            <div class="search-input"><x-icon name="search" /><label for="search" class="sr-only">Zoek op
                    naam</label><input type="search" id="search" name="search" value="{{ request('search') }}"
                    placeholder="Zoek een vaste last…" maxlength="100"></div><label for="category"
                class="sr-only">Filter op categorie</label><select id="category" name="category">
                <option value="">Alle categorieën</option>
                @foreach (\App\Enums\Category::cases() as $category)
                    <option value="{{ $category->value }}" @selected(request('category') === $category->value)>{{ $category->label() }}
                    </option>
                @endforeach
            </select>
            <button class="btn btn-secondary" type="submit"><x-icon name="sliders-horizontal" />Filteren</button>
            @if (request()->filled('search') || request()->filled('category'))
                <a class="text-link" href="{{ route('expenses.index', ['month' => $month->format('Y-m')]) }}">Wissen</a>
            @endif
        </form>
    @endif
    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>{{ $isExpense ? 'Alle vaste lasten' : 'Alle inkomstenbronnen' }}</h2>
                <p>{{ $entries->total() }} {{ $isExpense ? 'vaste lasten' : 'inkomstenbronnen' }} · inclusief
                    inactieve en geplande posten</p>
            </div><span class="soft-badge">{{ $entries->total() }} totaal</span>
        </div><x-entry-table :entries="$entries" :type="$type" :month="$month" />
    </section>
    <div class="pagination">{{ $entries->links() }}</div>
    <div class="calculation-note"><x-icon name="info" /><span>Het dashboard telt alleen actieve posten binnen de
            geselecteerde maand mee. Week-, kwartaal- en jaarbedragen worden omgerekend naar een maandgemiddelde.</span>
    </div>
</x-app-layout>
