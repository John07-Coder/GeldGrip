@props(['title' => 'Dashboard', 'month' => null])
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <meta name="description" content="Meer overzicht. Meer rust. Houd je inkomsten en vaste lasten bij met GeldGrip.">
    <title>{{ $title }} · GeldGrip</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <a class="skip-link" href="#main">Ga naar inhoud</a>
    <div class="sidebar-backdrop" data-close-menu hidden></div>
    <aside class="sidebar" id="sidebar" aria-label="Hoofdnavigatie">
        <div class="sidebar-brand"><x-brand /><button class="icon-button mobile-close" data-close-menu
                aria-label="Menu sluiten"><x-icon name="x" /></button></div>
        <div class="workspace-label">PERSOONLIJKE OMGEVING</div>
        <nav class="nav-list">
            @foreach ([['dashboard', 'layout-dashboard', 'Dashboard'], ['incomes.index', 'wallet', 'Inkomsten'], ['expenses.index', 'receipt-text', 'Vaste lasten']] as [$route, $icon, $label])
                <a href="{{ route($route, $month ? ['month' => $month->format('Y-m')] : []) }}"
                    @class([
                        'nav-link',
                        'active' => request()->routeIs(
                            $route === 'dashboard' ? 'dashboard' : explode('.', $route)[0] . '.*'),
                    ]) @if (request()->routeIs($route === 'dashboard' ? 'dashboard' : explode('.', $route)[0] . '.*')) aria-current="page" @endif>
                    <x-icon :name="$icon" /><span>{{ $label }}</span><x-icon name="chevron-right"
                        class="nav-arrow" />
                </a>
            @endforeach
        </nav>
        <div class="sidebar-bottom">
            <div class="sidebar-note"><span class="note-spark"><x-icon name="sprout" /></span><strong>Kleine
                    stappen.<br>Meer financiële rust.</strong>
                <p>Inzicht in je geld begint met een helder overzicht.</p>
            </div>
            <a href="{{ route('settings.edit') }}" @class(['nav-link', 'active' => request()->routeIs('settings.*')])><x-icon
                    name="settings-2" /><span>Instellingen</span></a>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="nav-link logout"
                    type="submit"><x-icon name="log-out" /><span>Uitloggen</span></button></form>
            <div class="sidebar-footer"><span class="status-dot"></span> Jouw geld. Jouw overzicht.</div>
        </div>
    </aside>
    <div class="app-main">
        <header class="topbar">
            <div class="topbar-title"><button class="icon-button menu-button" id="menu-toggle" aria-controls="sidebar"
                    aria-expanded="false" aria-label="Menu openen"><x-icon
                        name="menu" /></button><span>{{ $title }}</span><span
                    class="topbar-separator">/</span><span class="topbar-subtitle">Mijn overzicht</span></div>
            <div class="topbar-actions">
                @if ($month)
                    <form method="get"
                        action="{{ route(request()->routeIs('dashboard') ? 'dashboard' : (request()->routeIs('incomes.*') ? 'incomes.index' : 'expenses.index')) }}"
                        class="month-picker">
                        <x-icon name="calendar-days" /><label class="sr-only" for="month">Selecteer maand en
                            jaar</label>
                        <input type="month" name="month" id="month" value="{{ $month->format('Y-m') }}" required
                            aria-label="Selecteer maand en jaar">
                        <button type="submit" class="month-submit" aria-label="Geselecteerde maand tonen"><x-icon
                                name="arrow-right" /></button>
                    </form>
                @endif
                <a href="{{ route('settings.edit') }}" class="user-profile"><span
                        class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><span
                        class="user-detail"><strong>{{ auth()->user()->name }}</strong><small>Persoonlijk
                            account</small></span><x-icon name="chevron-down" /></a>
            </div>
        </header>
        <main id="main" class="page-content"><x-alerts />{{ $slot }}<footer class="page-footer"><span>©
                    {{ date('Y') }} GeldGrip</span><span>Meer overzicht. Meer rust.</span></footer>
        </main>
    </div>
    <dialog id="confirm-dialog" class="confirm-dialog" aria-labelledby="confirm-title">
        <div class="delete-icon"><x-icon name="trash-2" /></div>
        <h2 id="confirm-title">Weet je het zeker?</h2>
        <p id="confirm-message"></p>
        <div class="form-actions"><button type="button" class="btn btn-secondary"
                data-cancel-delete>Annuleren</button><button type="button" class="btn btn-danger"
                data-confirm-delete>Verwijderen</button></div>
    </dialog>
</body>

</html>
