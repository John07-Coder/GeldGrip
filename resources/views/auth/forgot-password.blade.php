<x-guest-layout title="Wachtwoord vergeten">
    <span class="eyebrow">WE HELPEN JE VERDER</span>
    <h1>Wachtwoord vergeten?</h1>
    <p class="auth-intro">Vul je e-mailadres in. Je ontvangt een link waarmee je een nieuw wachtwoord kunt kiezen.</p>
    <form method="post" action="{{ route('password.email') }}" class="form-stack">@csrf
        <x-field name="email" label="E-mailadres" type="email" autocomplete="email" required autofocus />
        <button class="btn btn-primary btn-full" type="submit">Herstellink aanvragen <x-icon
                name="arrow-right" /></button>
    </form>
    <p class="auth-switch"><a href="{{ route('login') }}">Terug naar inloggen</a></p>
</x-guest-layout>
