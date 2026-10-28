<x-guest-layout title="Registreren">
    <span class="eyebrow">EEN FRIS BEGIN</span>
    <h1>Krijg grip op je geld.</h1>
    <p class="auth-intro">Maak je account en zet de eerste stap naar overzicht.</p>
    <form method="post" action="{{ route('register') }}" class="form-stack">@csrf
        <x-field name="name" label="Je naam" placeholder="Voor- en achternaam" autocomplete="name" maxlength="100"
            required autofocus />
        <x-field name="email" label="E-mailadres" type="email" placeholder="jij@voorbeeld.nl"
            autocomplete="username" required />
        <x-field name="password" label="Wachtwoord" type="password" autocomplete="new-password"
            hint="Gebruik minimaal 8 tekens." minlength="8" required />
        <x-field name="password_confirmation" label="Herhaal je wachtwoord" type="password" autocomplete="new-password"
            required />
        <button class="btn btn-primary btn-full" type="submit">Account aanmaken <x-icon name="arrow-right" /></button>
    </form>
    <p class="auth-switch">Heb je al een account? <a href="{{ route('login') }}">Log in</a></p>
</x-guest-layout>
