<x-guest-layout title="Nieuw wachtwoord">
    <span class="eyebrow">EEN NIEUWE START</span>
    <h1>Kies een nieuw wachtwoord.</h1>
    <p class="auth-intro">Daarna kun je weer inloggen op je persoonlijke overzicht.</p>
    <form method="post" action="{{ route('password.update') }}" class="form-stack">@csrf<input type="hidden" name="token"
            value="{{ $token }}">
        <x-field name="email" label="E-mailadres" type="email" :value="request('email')" autocomplete="username" required />
        <x-field name="password" label="Nieuw wachtwoord" type="password" autocomplete="new-password" minlength="8"
            required />
        <x-field name="password_confirmation" label="Herhaal je wachtwoord" type="password" autocomplete="new-password"
            required />
        <button class="btn btn-primary btn-full" type="submit">Wachtwoord opslaan <x-icon
                name="arrow-right" /></button>
    </form>
</x-guest-layout>
