# GeldGrip

Persoonlijk financieel dashboard. Je houdt je netto-inkomen en vaste lasten bij en ziet wat er gemiddeld per maand overblijft. De schermafbeeldingen hieronder laten de applicatie zien zonder dat je hem hoeft te installeren.

## Schermafbeeldingen

### Dashboard

Netto maandinkomen, vaste lasten en wat er overblijft, plus een verdeling per categorie.

![Dashboard met netto-inkomen, vaste lasten en wat er overblijft](docs/screenshots/dashboard-desktop.jpg)

### Op je telefoon

Hetzelfde overzicht op een smal scherm.

![Dashboard op een telefoon](docs/screenshots/dashboard-mobile.jpg)

## Wat je ermee kunt

- Een account aanmaken, inloggen, uitloggen, ingelogd blijven en je wachtwoord herstellen.
- Meerdere inkomstenbronnen bijhouden.
- Vaste lasten beheren met categorie, frequentie, betaaldag en een begin- en einddatum.
- Per maand zien wat er overblijft, met een categorieoverzicht en een vergelijking.
- Zoeken, filteren, en je profiel of wachtwoord wijzigen. Je account verwijderen haalt ook de financiële gegevens weg.

## Techniek

Laravel 13, Blade, Tailwind CSS, Chart.js, SQLite en PHPUnit. Lokaal gaat e-mail naar `storage/logs/laravel.log`.

## Clonen en starten

Je hebt Git, PHP 8.3 of nieuwer, [Composer](https://getcomposer.org) en [Node.js](https://nodejs.org) 20 of nieuwer nodig.

```bash
git clone https://github.com/John07-Coder/GeldGrip.git
cd GeldGrip
composer run setup
php artisan serve
```

`composer run setup` maakt `.env`, de applicatiesleutel, `database/database.sqlite`, de migraties en de frontend. Open daarna [http://127.0.0.1:8000](http://127.0.0.1:8000) en registreer een account. Een nieuw account begint leeg.

Tijdens het ontwikkelen kun je `composer run dev` gebruiken. Dat start Laravel en Vite samen.

Een herstellink komt lokaal in `storage/logs/laravel.log`, omdat `MAIL_MAILER=log` staat. Voor echte e-mail zet je in `.env` de SMTP-gegevens en voer je `php artisan config:clear` uit.

### Voorbeeldgegevens

Alleen als `APP_ENV=local` kun je een demoaccount vullen. Kies zelf een wachtwoord. Er staat geen standaardwachtwoord in de code.

```bash
DEMO_PASSWORD='kies-een-eigen-wachtwoord' php artisan db:seed --class=DemoSeeder
```

Log in met `demo@example.test` en het gekozen wachtwoord. De schermafbeeldingen in deze repository zijn gemaakt met fictieve gegevens.

## Tests

```bash
php artisan test
```

## Beveiliging

- Je ziet alleen je eigen inkomsten en lasten. Een id van iemand anders levert 404 op.
- De client kan geen `user_id` meesturen. Laravel verzorgt de sessie, de wachtwoordhash en CSRF.
- Inloggen, registreren en wachtwoordherstel zijn beperkt in aantal pogingen per minuut.
