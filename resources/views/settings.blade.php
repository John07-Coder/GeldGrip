<x-app-layout title="Instellingen">
    <div class="page-heading">
        <div><span class="eyebrow">JOUW PERSOONLIJKE OMGEVING</span>
            <h1>Instellingen<span class="heading-dot">.</span></h1>
            <p>Beheer je profiel en de toegang tot je account.</p>
        </div>
    </div>
    <div class="settings-grid">
        <section class="panel form-panel appearance-panel">
            <h2>Weergave</h2><p class="section-description">Je keuze wordt direct toegepast en op dit apparaat onthouden.</p>
            <fieldset class="theme-options"><legend class="sr-only">Kies je weergave</legend>
                @foreach ([['light', 'sun', 'Licht'], ['dark', 'moon', 'Donker'], ['system', 'monitor', 'Systeemvoorkeur']] as [$value, $icon, $label])
                    <label class="theme-option"><input type="radio" name="theme" value="{{ $value }}" data-theme-option @checked($value === 'system')><x-icon :name="$icon" /><span>{{ $label }}</span></label>
                @endforeach
            </fieldset><p class="theme-status" id="theme-status" role="status" aria-live="polite"></p>
        </section>
        <section class="panel form-panel">
            <h2>Je profiel</h2>
            <p class="section-description">Zo herkennen we je in GeldGrip.</p>
            <form method="post" action="{{ route('settings.update') }}" class="form-stack">@csrf @method('PATCH')<x-field
                    name="name" label="Je naam" :value="auth()->user()->name" maxlength="100" autocomplete="name"
                    required /><x-field name="email" label="E-mailadres" type="email" :value="auth()->user()->email"
                    autocomplete="email" required />
                <div><button class="btn btn-primary">Profiel opslaan</button></div>
            </form>
        </section>
        <section class="panel form-panel">
            <h2>Wachtwoord wijzigen</h2>
            <p class="section-description">Kies een wachtwoord van minimaal 8 tekens.</p>
            <form method="post" action="{{ route('settings.password') }}" class="form-stack">@csrf
                @method('PUT')<x-field name="current_password" label="Huidig wachtwoord" type="password"
                    autocomplete="current-password" required /><x-field name="password" label="Nieuw wachtwoord"
                    type="password" autocomplete="new-password" minlength="8" required /><x-field
                    name="password_confirmation" label="Herhaal nieuw wachtwoord" type="password"
                    autocomplete="new-password" required />
                <div><button class="btn btn-primary">Wachtwoord wijzigen</button></div>
            </form>
        </section>
        <section class="panel form-panel danger-panel">
            <h2>Account verwijderen</h2>
            <p class="section-description">Je account, inkomsten, vaste lasten en losse uitgaven worden definitief verwijderd. Vul je
                wachtwoord in om door te gaan.</p>
            <form method="post" action="{{ route('settings.destroy') }}" class="form-stack"
                data-confirm="Je account en alle financiële gegevens worden definitief verwijderd. Wil je doorgaan?">
                @csrf @method('DELETE')<x-field name="current_password" label="Bevestig met je wachtwoord"
                    type="password" id="delete_password" autocomplete="current-password" required />
                <div><button class="btn btn-danger" type="submit"><x-icon name="trash-2" />Account verwijderen</button>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
