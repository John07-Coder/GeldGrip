@props(['title'])
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <title>{{ $title }} · GeldGrip</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="auth-body">
    <aside class="auth-story"><x-brand />
        <div class="auth-story-content"><span class="eyebrow">GRIP OP JE GELD</span>
            <h1>Meer overzicht.<br>Meer ruimte.<br><span>Meer rust.</span></h1>
            <p>Je inkomen en vaste lasten op één plek. Ontdek wat je iedere maand overhoudt en kijk met vertrouwen
                vooruit.</p>
            <div class="auth-illustration">
                <div class="illustration-icon"><x-icon name="chart-no-axes-combined" /></div>
                <div><span>Een helder begin</span><strong>Jouw financiële overzicht</strong></div><x-icon
                    name="circle-check" class="illustration-check" />
            </div>
            <div class="auth-feature"><x-icon name="lock-keyhole" /><span>Een persoonlijke omgeving voor jouw
                    gegevens</span></div>
        </div><small>GeldGrip · Kleine stappen, meer financiële rust.</small>
    </aside>
    <main class="auth-main">
        <div class="auth-mobile-brand"><x-brand /></div>
        <div class="auth-card"><x-alerts />{{ $slot }}</div>
        <p class="auth-footer">Meer inzicht begint met één kleine stap.</p>
    </main>
</body>

</html>
