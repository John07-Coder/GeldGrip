@props(['entries', 'month', 'dashboard' => false])
@use('App\Support\Money')
@if ($entries->isEmpty())
    <div class="empty-state">
        <span class="empty-icon"><x-icon name="shopping-basket" /></span>
        <h3>{{ request()->filled('search') || request()->filled('category') ? 'Geen resultaten gevonden' : 'Nog geen uitgaven deze maand' }}</h3>
        <p>Registreer losse aankopen voor een completer beeld van je budget. Vaste lasten beheer je apart.</p>
        <a class="btn btn-secondary" href="{{ route('spending.create', ['month' => $month->format('Y-m'), 'return_to' => $dashboard ? 'dashboard' : null]) }}"><x-icon name="plus" />Uitgave toevoegen</a>
    </div>
@else
    <table class="entries-table spending-table">
        <thead><tr><th scope="col">Naam</th><th scope="col">Categorie</th><th scope="col">Datum</th><th scope="col">Bedrag</th><th scope="col" class="actions-heading">Acties</th></tr></thead>
        <tbody>
        @foreach ($entries as $entry)
            <tr>
                <td class="entry-name"><span class="entry-category-icon" style="--category-color:{{ $entry->category->color() }}"><x-icon :name="$entry->category->icon()" /></span><div><strong>{{ $entry->name }}</strong>@if ($entry->description)<small class="expense-description">{{ $entry->description }}</small>@endif</div></td>
                <td data-label="Categorie"><span class="category-badge" style="--category-color:{{ $entry->category->color() }}">{{ $entry->category->label() }}</span></td>
                <td data-label="Datum"><time datetime="{{ $entry->expense_date->toDateString() }}">{{ $entry->expense_date->translatedFormat('j M Y') }}</time></td>
                <td data-label="Bedrag" class="amount-cell"><strong>{{ Money::format($entry->cents()) }}</strong></td>
                <td class="row-actions">
                    <a class="icon-button" href="{{ route('spending.edit', [$entry, 'month' => $month->format('Y-m'), 'return_to' => $dashboard ? 'dashboard' : null]) }}" aria-label="{{ $entry->name }} bewerken" title="Bewerken"><x-icon name="pencil" /></a>
                    <form method="post" action="{{ route('spending.destroy', [$entry, 'month' => $month->format('Y-m'), 'return_to' => $dashboard ? 'dashboard' : null]) }}" data-confirm="Wil je ‘{{ $entry->name }}’ verwijderen? Deze actie kun je niet ongedaan maken.">
                        @csrf @method('DELETE')<button class="icon-button delete-button" type="submit" aria-label="{{ $entry->name }} verwijderen" title="Verwijderen"><x-icon name="trash-2" /></button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
