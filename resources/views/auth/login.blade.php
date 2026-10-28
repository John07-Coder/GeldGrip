<x-guest-layout title="Inloggen">
    <span class="eyebrow">WELKOM TERUG</span>
    <h1>Goed je weer te zien.</h1>
    <p class="auth-intro">Log in en bekijk hoe je er deze maand voor staat.</p>
    <form method="post" action="{{ route('login') }}" class="form-stack">@csrf
        <x-field name="email" label="E-mailadres" type="email" placeholder="jij@voorbeeld.nl" autocomplete="username"
            required autofocus />
        <x-field name="password" label="Wachtwoord" type="password" placeholder="Je wachtwoord"
            autocomplete="current-password" required />
        <div class="form-inline"><label class="checkbox"><input type="checkbox" name="remember" value="1"> Ingelogd
                blijven</label><a href="{{ route('password.request') }}">Wachtwoord vergeten?</a></div>
        <button class="btn btn-primary btn-full" type="submit">Inloggen <x-icon name="arrow-right" /></button>
    </form>
    <p class="auth-switch">Nog geen account? <a href="{{ route('register') }}">Maak gratis een account</a></p>
</x-guest-layout>
