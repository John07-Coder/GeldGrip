# GeldGrip 2.0 — implementatie en verificatie

## Uitbreidingen

De bestaande Laravel 13-, Blade-, Tailwind 4-, Chart.js- en Lucide-structuur is behouden. De bestaande inkomsten- en vaste-lastenroutes blijven werken. De lokale databaseverbinding is ongewijzigd; de aanwezige installatie gebruikt SQLite. De nieuwe migratie gebruikt ook met MySQL compatibele Laravel-schemadefinities.

- **Uitgaven** (`/uitgaven`): toevoegen, bewerken en verwijderen met bevestigingsdialoog; acht categorieën met iconen en kleuren; maandselectie; zoeken; categoriefilter; sorteren op datum of bedrag in beide richtingen; paginering per 15 records.
- **Statistieken**: totaal, aantal, grootste categorie en gemiddelde van de hele geselecteerde maand. De lijstfilters veranderen deze maandstatistieken niet; dit staat op het scherm vermeld. Donut per categorie en een grafiek over zes maanden, eindigend bij de geselecteerde maand.
- **Dashboard**: vier kaarten, aangepaste vergelijking en de vijf recentste losse uitgaven van de geselecteerde maand. Het bestaande vaste-lastenoverzicht blijft beschikbaar.
- **Budgetsimulator** (`/budgetsimulator`): inkomen via invoerveld of slider, bestaande maandgemiddelden aanpassen of uitschakelen, hypothetische lasten toevoegen en verwijderen, variabele categoriebedragen aanpassen, live vergelijking, verschil en Chart.js-grafiek. Een leeg account kan handmatig beginnen.
- **Weergave**: licht, donker en systeemvoorkeur; directe toggle in beide layouts; instellingen met drie opties; localStorage, synchronisatie tussen tabbladen en systeemwijzigingen. Een vroeg script zet het thema vóór de stylesheets zodat de verkeerde achtergrond niet eerst wordt getoond. Grafiekkleuren veranderen mee zonder reload.
- **UX**: Nederlandse meldingen, eurobedragen en datums, mobiele navigatie, invoervalidatie, lege toestanden, focusstijlen, verminderde animatie bij apparaatvoorkeur en loading states bij opslaan/resetten.

## Database en autorisatie

Alleen `2026_10_29_000001_create_expenses_table.php` is toegevoegd. `expenses` bevat de gebruiker, naam, DECIMAL(12,2)-bedrag, categorie, datum, optionele beschrijving en timestamps. Indexen ondersteunen gebruiker/maand en gebruiker/categorie/maand. De foreign key verwijdert uitgaven bij accountverwijdering.

`User::expenses()` en `Expense::user()` zijn toegevoegd. Alle lees- en schrijfacties lopen via de relatie van de ingelogde gebruiker, volgens het bestaande autorisatiepatroon. Andermans record geeft 404. `user_id` kan niet via het formulier worden toegewezen. Authenticatie, CSRF en Blade-escaping blijven actief.

## Rekenregels

`FinancialOverview` gebruikt voortaan:

**Resterend budget = netto maandinkomen − gemiddelde vaste lasten − geregistreerde variabele uitgaven in de geselecteerde maand.**

De bestaande `Money`-berekeningen en frequentieomrekening blijven leidend. Vaste lasten worden eenmaal per post afgerond naar hele centen. Losse uitgaven worden eenmaal opgeteld op basis van hun datum, zonder frequentieomrekening. `ExpenseOverview` telt en groepeert bedragen in hele centen en rondt het gemiddelde half naar boven af. De zesmaandengrafiek bevat ook maanden zonder uitgaven.

De simulator ontvangt dezelfde uitgangsgegevens uit `FinancialOverview`. De browser werkt met een diepe kopie en gehele centen. Nul is toegestaan in een scenario; gewone opgeslagen uitgaven moeten strikt positief zijn. Negatieve resterende bedragen zijn toegestaan. Ongeldige scenario-invoer markeert het veld en behoudt de laatste geldige vergelijking. De resetknop haalt de actuele uitgangsgegevens opnieuw op via een GET-verzoek. Er bestaat geen schrijfroute voor simulaties en scenario's worden niet opgeslagen.

Vaste lasten worden niet automatisch gekopieerd naar losse uitgaven. De formulieren leggen uit dat een betaling die al als vaste last is opgenomen niet opnieuw als losse uitgave moet worden geregistreerd. Er is geen bankkoppeling of automatische herkenning van handmatig dubbel ingevoerde betalingen. Het resterende bedrag is geen banksaldo; niet-geregistreerde aankopen zijn onbekend.

## Automatische verificatie

Op 29 oktober 2026 uitgevoerd:

- `php artisan test --compact`: **64 geslaagde tests, 410 assertions**, inclusief de 43 bestaande tests.
- `npm test`: **6 geslaagde JavaScript-tests**, met Node's ingebouwde testrunner en zonder extra dependencies.
- `npm run build`: geslaagde productiebuild.
- `php artisan view:cache`: alle Blade-templates compileren.
- `php artisan route:list --except-vendor`: 34 routes; bestaande routes behouden.
- Laravel Pint: gewijzigde en nieuwe PHP-bestanden voldoen aan de formattering.
- `php artisan migrate --force`: alleen de nieuwe uitgavenmigratie toegepast op de lokale applicatiedatabase.

Nieuwe tests controleren CRUD, Nederlandse komma-invoer, maandgrenzen, zes maanden inclusief nulmaanden, categorieën, gemiddelde centafronding, gebruikersisolatie, filters, sortering, paginering, ongeldige invoer bij toevoegen en wijzigen, cascade delete en authenticatie. Het dashboardvoorbeeld controleert € 2.500 − € 700 − € 450 = € 1.350, inclusief de omrekening van een jaarabonnement naar € 10 per maand.

Simulatietests controleren gewijzigde inkomsten, hypothetische lasten, uitschakelen, nulbedragen, negatieve uitkomsten, reset, actuele resetgegevens en ongewijzigde databasegegevens. Thematests controleren bewaren en opnieuw laden, systeemvoorkeur, apparaatwijzigingen, synchronisatie tussen tabbladen, ongeldige voorkeuren en onbeschikbare localStorage. Featuretests controleren de vroege thema-initialisatie en toggle in alle pagina-layouts.

De Laravel-suite draait op SQLite in het geheugen. De nieuwe versie is niet afzonderlijk op een MySQL-server getest.

## Browsercontrole

De browsercontrole gebruikt een aparte tijdelijke SQLite-database met uitsluitend fictieve gegevens. De echte financiële gegevens worden hiervoor niet gewijzigd.

Gecontroleerd: dashboardtotalen, dark-modegrafieken, inkomen aanpassen, hypothetische autokosten, vaste last uitschakelen, variabele categorie op nul, ongeldige scenario-invoer, negatief budget en reset. Ook gecontroleerd: negatieve uitgave geweigerd, uitgave toevoegen met komma-invoer, bedrag en datum wijzigen, verhuizing naar de juiste maand, verwijderbevestiging annuleren en thema behouden na navigatie en vernieuwen.

Mobiele controles gebruiken 390 × 844 en 320 × 780 pixels. De nieuwe pagina's blijven binnen de schermbreedte en de mobiele navigatie toont beide nieuwe onderdelen. Login, registratie, wachtwoordherstel, inkomsten, vaste lasten, hun formulieren, uitgaven, simulator en instellingen zijn in dark mode bekeken. De gecontroleerde pagina's tonen geen onverwachte witte formulier- of kaartachtergronden; de browserconsole bevat geen JavaScript-fouten of waarschuwingen. Schermafbeeldingen met fictieve gegevens staan in `docs/screenshots/simulator-dark-v2.jpg` en `expenses-mobile-v2.jpg`.

## Gebruik

De lokale migratie en build zijn uitgevoerd. Bij installatie op een andere omgeving:

```powershell
php artisan migrate --force
npm ci
npm run build
php artisan test
npm test
```

De thema-instelling geldt per apparaat/browser en wordt niet tussen apparaten gesynchroniseerd. Als browseropslag is geblokkeerd blijft wisselen werken, maar kan de voorkeur na vernieuwen niet worden onthouden. Productiehosting en SMTP blijven zoals in de bestaande installatie ingericht.
