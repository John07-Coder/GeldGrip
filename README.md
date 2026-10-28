# GeldGrip

Een persoonlijk financieel dashboard in Laravel 13, PHP, Blade en Tailwind CSS. Registreer een account, voeg je netto inkomsten en vaste lasten toe en bekijk wat gemiddeld per maand overblijft.

![Dashboard met uitsluitend fictieve testgegevens](docs/screenshots/dashboard-desktop.jpg)

## Wat werkt

- Registreren, inloggen, uitloggen, ingelogd blijven en wachtwoordherstel.
- Meerdere inkomstenbronnen aanmaken, bewerken, deactiveren en verwijderen.
- Vaste lasten beheren met categorie, frequentie, betaaldag, startdatum en optionele einddatum.
- Bevestiging vóór verwijderen; validatie en meldingen in het Nederlands.
- Maand- en jaarselectie, categorieoverzicht en vergelijking met Chart.js.
- Resterend bedrag, percentage over, grootste vaste last en tekortwaarschuwing.
- Zoeken en categoriefilters, paginering, lege toestanden en mobiele navigatie.
- Profiel bijwerken, wachtwoord wijzigen en account met bijbehorende financiële gegevens verwijderen.

## Vereisten

- PHP 8.3 of hoger, met PDO SQLite, mbstring, XML, cURL, OpenSSL en de gebruikelijke Laravel-extensies.
- Composer 2, Node.js 22.12 of hoger en npm.
- SQLite: ingebouwd in de aanwezige PHP-installatie. Een aparte databaseserver is niet nodig.

De implementatie gebruikt PHP 8.4 en Laravel 13.39. De app slaat gegevens standaard op in `database/database.sqlite`. Dit bestand wordt niet in Git opgenomen. MySQL blijft optioneel ondersteund.

## Installeren

Open een terminal in de projectmap en installeer de dependencies:

```powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
```

De meegeleverde `.env.example` gebruikt SQLite:

```dotenv
APP_NAME=GeldGrip
APP_URL=http://127.0.0.1:8000
APP_LOCALE=nl
DB_CONNECTION=sqlite
```

Laat `DB_DATABASE` weg: Laravel gebruikt dan automatisch het absolute pad naar `database/database.sqlite`. Maak dit bestand aan als het nog niet bestaat en voer de migraties uit. Bestaande gegevens worden behouden.

```powershell
if (-not (Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File }
php artisan migrate
npm run build
php artisan serve --host=127.0.0.1
```

Open **http://127.0.0.1:8000** en registreer je eigen account. Nieuwe accounts beginnen zonder financiële gegevens. Er zijn geen vaste voorbeeldbedragen in de dashboardlogica.

Tijdens ontwikkelen kun je na de installatie `composer run dev` gebruiken. Dat start Laravel en Vite samen, ook op Windows. SQLite werkt direct vanuit het lokale bestand; XAMPP/MySQL starten is niet nodig. Een queueworker is voor de huidige functies niet nodig. `composer run setup` maakt bij een nieuwe installatie ook het SQLite-bestand aan.

### Wachtwoordherstel en e-mail

Standaard staat `MAIL_MAILER=log`: de herstellink wordt lokaal in `storage/logs/laravel.log` geschreven. Voor echte e-mails configureer je `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` en de gewenste TLS-instellingen. Daarna voer je `php artisan config:clear` uit. Een herstellink is eenmalig en verloopt na de door Laravel ingestelde termijn.

### Optionele fictieve demogegevens

Alleen in `APP_ENV=local` kun je een demoaccount maken. Kies zelf een wachtwoord; er staat geen standaard demowachtwoord in de code.

```powershell
$env:DEMO_PASSWORD='kies-een-eigen-demo-wachtwoord'
php artisan db:seed --class=DemoSeeder
Remove-Item Env:DEMO_PASSWORD
```

Log in met `demo@example.test` en het gekozen wachtwoord. `DatabaseSeeder` is leeg; gewone installatie en productie krijgen geen demogegevens. De screenshots in deze repository zijn gemaakt met uitsluitend fictieve, tijdelijke testgegevens.

## Architectuur

De app volgt Laravel MVC zonder extra architectuurlagen:

| Onderdeel | Verantwoordelijkheid |
| --- | --- |
| `routes/web.php` | Named routes, resource routes, authenticatie en throttling |
| `app/Models/User.php` | Gebruiker met `incomeSources()` en `fixedExpenses()` |
| `app/Models/IncomeSource.php`, `FixedExpense.php` | Eloquent-modellen, decimal- en datumcasts |
| `app/Models/Concerns/HasMonthlyAmount.php` | Gedeelde maandberekening en periodefilter |
| `app/Http/Controllers/` | Authenticatie, CRUD, dashboard en instellingen |
| `app/Http/Requests/` | Validatie van bedragen, datums, categorieën en filters |
| `app/Services/FinancialOverview.php` | Totalen, categorieën, percentage en grootste last |
| `app/Support/Money.php` | Omrekening en afronding in hele centen; Nederlandse opmaak |
| `app/Enums/` | Toegestane frequenties en categorieën |
| `resources/views/` | Blade-pagina's en herbruikbare layout-, tabel- en formuliercomponenten |
| `resources/css/app.css` | Tailwind-import en responsive componentstijlen |
| `resources/js/` | Lucide-iconen, mobiele navigatie, bevestigingsdialoog en Chart.js |
| `database/migrations/` | Tabellen, indexen en foreign keys met cascade delete |
| `lang/nl/` | Nederlandse validatie- en herstelmeldingen |

Inkomsten en lasten worden altijd via de relatie van de ingelogde gebruiker opgehaald. Een ID van iemand anders levert 404 op, ook bij bewerken en verwijderen. De client kan `user_id` niet toewijzen. Laravel verzorgt sessies, wachtwoordhashing en CSRF-bescherming; Blade escaped gebruikersinvoer. Inloggen, registratie en herstelverzoeken hebben limieten tegen herhaald misbruik.

Het dashboard gebruikt twee queries voor de financiële records en groepeert de geladen records in het geheugen. Er worden geen gebruikersrelaties per rij geladen.

## Rekenregels

De migraties gebruiken `DECIMAL(12,2)` en Eloquent leest bedragen als decimale strings met twee decimalen uit. SQLite gebruikt hiervoor zijn numerieke opslagtype; MySQL gebruikt een DECIMAL-kolom. `Money` zet de uitgelezen bedragen om naar gehele centen; geldberekeningen en optellingen gebruiken geen floats. Alleen de weergave van percentages en grafieken gebruikt delingen voor presentatiedoeleinden.

| Frequentie | Gemiddeld maandbedrag |
| --- | --- |
| Wekelijks | bedrag × 52 ÷ 12 |
| Maandelijks | bedrag |
| Per kwartaal | bedrag ÷ 3 |
| Jaarlijks | bedrag ÷ 12 |

Elke post wordt eenmaal half naar boven afgerond op een hele cent. Daarna worden de maandbedragen opgeteld. Het resterende bedrag is inkomen min vaste lasten. Het percentage is resterend bedrag gedeeld door inkomen; bij nul inkomen staat er een streepje.

Een actieve post telt mee als `start_date` op of vóór de laatste dag van de geselecteerde maand valt en `end_date` leeg is of op of na de eerste dag valt. De grenzen zijn inclusief. Ook bij een gedeeltelijke maand wordt het volledige gemiddelde gebruikt: deze eerste versie rekent niet naar rato. De schakelaar actief/inactief geldt voor de hele post; er wordt geen geschiedenis van afzonderlijke activeringen bijgehouden.

Een betaaldag is een informatief dagnummer, geen betaling of bankkoppeling. Jaarbedragen zijn maandgemiddelden, geen fictieve maandelijkse afschrijvingen. Het bedrag na vaste lasten is geen banksaldo en houdt geen rekening met boodschappen of andere dagelijkse uitgaven.

## Tests

De tests gebruiken standaard een tijdelijke SQLite-database in het geheugen:

```powershell
php artisan test
```

De suite controleert authenticatie en herstel, CRUD, activeren/deactiveren, geldberekeningen, week-/jaaromrekening, afronding, periodegrenzen, gebruikersisolatie, ongeldige invoer, filters, maandwisseling, profielwijzigingen en cascade delete.

Optioneel is dezelfde suite ook uitvoerbaar op MySQL/MariaDB met PHP-extensie PDO MySQL. Deze is eerder gecontroleerd tegen MariaDB 10.4.32. Voor een herhaling maak je eerst een **aparte, lege testdatabase** aan en configureer je de MySQL-host, gebruikersnaam en het wachtwoord voor de testopdracht. `RefreshDatabase` beheert het schema en de testgegevens; gebruik hiervoor nooit je applicatiedatabase.

```sql
CREATE DATABASE geldgrip_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```powershell
$env:DB_CONNECTION='mysql'
$env:DB_DATABASE='geldgrip_test'
php artisan test
Remove-Item Env:DB_CONNECTION
Remove-Item Env:DB_DATABASE
```

Aanvullende controles:

```powershell
npm run build
php artisan view:cache
composer validate --no-check-publish
composer audit
npm audit
```

Zie [de verificatie en het bestandsoverzicht](docs/IMPLEMENTATIE.md) voor de uitgevoerde controles.

## Hosting

De app is lokaal geïmplementeerd. Voor publicatie is PHP-hosting met SQLite-ondersteuning nodig, met `public/` als document root. De applicatie moet de database en de map `database/` kunnen beschrijven. Stel onder meer `APP_ENV=production`, `APP_DEBUG=false`, de echte HTTPS-URL, `SESSION_SECURE_COOKIE=true` en SMTP in. MySQL is ook mogelijk door de databaseverbinding en verbindingsgegevens in `.env` aan te passen. Bouw de frontend, voer migraties uit en cache de Laravel-configuratie volgens je hostingprocedure. `.env`, databases, logs en `vendor/` horen niet in een publieke webdirectory.

Productiehosting en een echte SMTP-verbinding zijn niet in dit project geconfigureerd. Bankkoppelingen, transacties en historische banksaldi vallen buiten de gevraagde eerste versie.
