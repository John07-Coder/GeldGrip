@props(['entries', 'type', 'month', 'dashboard' => false])
@use('App\Support\Money')
@php($isExpense = $type === 'expense')
@php($routePrefix = $isExpense ? 'expenses' : 'incomes')
@if ($entries->isEmpty())
    <div class="empty-state">
        <span class="empty-icon"><x-icon :name="$isExpense ? 'receipt-text' : 'wallet'" /></span>
        <h3>{{ request()->filled('search') || request()->filled('category') ? 'Geen resultaten gevonden' : ($isExpense ? 'Nog geen vaste lasten' : 'Een goed overzicht begint met je inkomen') }}
        </h3>
        <p>{{ request()->filled('search') || request()->filled('category') ? 'Probeer een andere zoekopdracht of categorie.' : ($isExpense ? 'Van huur tot je favoriete abonnement. Voeg je vaste lasten toe.' : 'Voeg je salaris, bijbaan of een andere inkomstenbron toe.') }}
        </p><a class="btn btn-secondary"
            href="{{ route($routePrefix . '.create', ['month' => $month->format('Y-m'), 'return_to' => $dashboard ? 'dashboard' : null]) }}"><x-icon
                name="plus" />{{ $isExpense ? 'Vaste last toevoegen' : 'Inkomen toevoegen' }}</a>
    </div>
@else
    <table class="entries-table">
        <thead>
            <tr>
                <th>Naam</th>
                @if ($isExpense)
                    <th>Categorie</th>
                @endif
                <th>
                    Bedrag</th>
                <th>Frequentie</th>
                @if ($isExpense)
                    <th>Betaaldag</th>
                @endif @unless ($dashboard)
                <th>Status</th>
            @endunless
            <th class="actions-heading">
                Acties</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($entries as $entry)
            <tr>
                <td class="entry-name"><span class="entry-category-icon"
                        @if ($isExpense) style="--category-color:{{ $entry->category->color() }}" @endif><x-icon
                            :name="$isExpense ? $entry->category->icon() : 'wallet'" /></span>
                    <div><strong>{{ $entry->name }}</strong><small>{{ $dashboard ? 'Vaste uitgave' : 'Vanaf ' . $entry->start_date->format('d-m-Y') }}
                            @if (!$dashboard && $entry->end_date)
                                · tot {{ $entry->end_date->format('d-m-Y') }}
                            @endif
                        </small>
                    </div>
                </td>
                @if ($isExpense)
                    <td data-label="Categorie"><span class="category-badge">{{ $entry->category->label() }}</span>
                    </td>
                @endif
                <td data-label="Bedrag" class="amount-cell">
                    <strong>{{ Money::format(Money::cents($entry->amount)) }}</strong>
                    @if ($entry->frequency !== \App\Enums\Frequency::Monthly)
                        <small>Gem. {{ Money::format($entry->monthlyCents()) }} / mnd</small>
                    @endif
                </td>
                <td data-label="Frequentie">{{ $entry->frequency->label() }}</td>
                @if ($isExpense)
                    <td data-label="Betaaldag">{{ $entry->payment_day ? 'Dag ' . $entry->payment_day : '—' }}</td>
                @endif
                @unless ($dashboard)
                    <td data-label="Status"><span
                            @class(['status-badge', 'inactive' => !$entry->is_active])>{{ $entry->is_active ? 'Actief' : 'Inactief' }}</span></td>
                @endunless
                <td class="row-actions"><a class="icon-button"
                        href="{{ route($routePrefix . '.edit', [$entry, 'month' => $month->format('Y-m'), 'return_to' => $dashboard ? 'dashboard' : null]) }}"
                        aria-label="{{ $entry->name }} bewerken" title="Bewerken"><x-icon name="pencil" /></a>
                    <form method="post"
                        action="{{ route($routePrefix . '.destroy', [$entry, 'month' => $month->format('Y-m'), 'return_to' => $dashboard ? 'dashboard' : null]) }}"
                        data-confirm="Wil je ‘{{ $entry->name }}’ verwijderen? Deze actie kun je niet ongedaan maken.">
                        @csrf @method('DELETE')<button type="submit" class="icon-button delete-button"
                            aria-label="{{ $entry->name }} verwijderen" title="Verwijderen"><x-icon
                                name="trash-2" /></button></form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif
