# Budgetten, voorspellingen en actuele schermafbeeldingen

## Functionaliteit

De uitbreiding gebruikt de bestaande Laravel MVC-structuur, uitgavencategorieën, `Money`, maandgemiddelden, Blade-componenten, Chart.js en thema's. De bestaande inkomsten, vaste lasten, uitgaven en simulator blijven beschikbaar.

- **Budgetten**: één budget per gebruiker, categorie, maand en jaar; toevoegen, wijzigen en verwijderen; maandselectie; vier statistieken; actuele uitgaven per categorie; voortgang en waarschuwingen; bedragen van de vorige maand overnemen zonder bestaande budgetten te overschrijven of uitgaven te kopiëren.
- **Voorspellingen**: standaard zes toekomstige maanden, direct omschakelen naar drie of twaalf; lijn- en tabeloverzicht; tijdelijke aannames voor inkomsten, vaste lasten en variabele uitgaven; reset naar actuele standaardgegevens.
- **Dashboard**: maximaal drie budgetten met de hoogste gebruikspercentages en het verwachte cumulatieve overschot over zes maanden.
- **README**: actuele desktop- en mobiele dashboardfoto, nieuwe budgetten- en voorspellingenfoto en een vernieuwde simulatorfoto. Alle beelden gebruiken dezelfde fictieve gebruiker in een aparte tijdelijke SQLite-database. De mobiele afbeelding wordt op GitHub met een leesbare, beperkte breedte weergegeven.

## Rekenen met budgetten

Het totaal uitgegeven bevat alleen losse uitgaven in categorieën met een budget in de geselecteerde maand. Vaste lasten en uitgaven in ongebudgetteerde categorieën worden niet van deze budgetten afgetrokken. Het bedrag buiten de ingestelde budgetten staat expliciet op de pagina.

Per categorie: beschikbaar = budget − geregistreerde uitgaven. Het werkelijke percentage kan boven 100% komen; de balk blijft maximaal 100% breed. Onder 70% is de balk groen, vanaf 70% oranje en vanaf 90% rood. Vanaf 80% verschijnt een waarschuwing, bij precies 100% een melding dat het budget gebruikt is en daarboven een melding met het overschreden bedrag.

Bedragen zijn opgeslagen als DECIMAL(12,2) en worden in hele centen opgeteld. De unieke databaseconstraint op gebruiker, categorie, maand en jaar beschermt naast formuliervalidatie tegen dubbele budgetten. Kopiëren gebruikt `firstOrCreate` in een transactie en behoudt bestaande bedragen. De categoriebedragen worden bij iedere weergave opnieuw uit de geregistreerde uitgaven berekend.

## Rekenen met voorspellingen

De maandkiezer is de referentiemaand; de projectie begint bij de daaropvolgende kalendermaand. Bij oktober 2026 begint de voorspelling dus in november 2026. Het cumulatieve resultaat begint bij € 0 en is geen banksaldo.

De standaard schatting voor variabele uitgaven gebruikt maximaal de laatste drie afgeronde kalendermaanden met geregistreerde uitgaven vóór de referentiemaand. De huidige, nog niet afgeronde kalendermaand en toekomstige uitgaven worden altijd uitgesloten, ook bij een toekomstige referentiemaand. Ontbrekende maanden worden niet verondersteld nulmaanden te zijn. Het scherm vermeldt de gebruikte maanden en hun aantal. Zonder historie is de schatting € 0 en nodigt de pagina uit om zelf een bedrag in te voeren. Het gemiddelde wordt eenmaal half naar boven afgerond op een hele cent.

Iedere toekomstige maand krijgt zijn eigen inkomsten en vaste lasten op basis van de bestaande frequentieomrekening en inclusieve begin- en einddatums. Inactieve posten tellen niet mee. Zoals in het bestaande dashboard telt een deels actieve maand volledig mee als maandgemiddelde; er wordt niet naar rato gerekend.

Een leeg aannamenveld volgt de standaardwaarden en bekende datums. Een ingevuld bedrag vervangt die geldstroom tijdelijk voor **alle** voorspelde maanden, ook als geregistreerde posten later stoppen. Dit staat bij de invoervelden vermeld. Nul is een geldige aanname; negatieve en ongeldige invoer wordt geweigerd. Negatieve maandresultaten en cumulatieve resultaten blijven toegestaan.

De maandkaarten tonen de eerste voorspelde maand. De tabel en de cumulatieve kaart gebruiken alle maanden afzonderlijk. Zo blijven veranderingen door bekende datums zichtbaar.

Voorspellingen hebben uitsluitend een GET-route en geen opslagtabel. Aanpassingen schrijven geen financiële gegevens. Herstellen haalt opnieuw de huidige standaardgegevens op en behoudt de gekozen horizon. De berekening van tijdelijke aannames gebruikt in de browser dezelfde centparser als de bestaande simulator. Een GET met aannames wordt ook op de server gevalideerd en berekend; de basisweergave en periodekeuze werken zonder JavaScript.

## Bestanden

- `CategoryBudget`, `CategoryBudgetRequest`, `CategoryBudgetController` en `BudgetOverview`: opslag, validatie, autorisatie, kopiëren en berekeningen.
- `2026_10_31_000001_create_category_budgets_table.php`: de enige nieuwe migratie, met foreign key, cascade delete, unieke constraint en maandindex.
- `FinancialForecast`, `ForecastRequest` en `ForecastController`: historische schatting, periodeberekening en alleen-lezen endpoint.
- `HasMonthlyAmount::isRelevantTo()`: dezelfde actieve periodegrenzen voor reeds geladen inkomsten en vaste lasten.
- `budgets/`, `budget-progress.blade.php`, `forecasts.blade.php`, dashboard en app-layout: pagina's, voortgang, navigatie en maandoverzichten.
- `forecast.js`, `budget.js`, `app.js` en CSS: live bediening, gedeelde centberekening, iconen en responsive themaondersteuning.
- `CategoryBudgetTest`, `FinancialForecastTest` en `forecast.test.js`: nieuwe automatische controles.
- `README.md` en `docs/screenshots/`: actuele uitleg en beelden.

Alle nieuwe lees- en schrijfacties zijn gebruikersgebonden. Andermans budget geeft 404. De browser kan `user_id` niet toewijzen. Accountverwijdering verwijdert bijbehorende budgetten via de foreign key. Bestaande authenticatie, CSRF en escaping blijven actief.

## Verificatie op 31 oktober 2026

- `php artisan test --compact`: **94 tests en 644 assertions geslaagd**, inclusief alle bestaande tests.
- `npm test`: **9 tests geslaagd**.
- `npm run build`: productiebuild geslaagd.
- `php artisan view:cache`: alle Blade-templates compileren.
- Laravel Pint: formattering van gewijzigde en nieuwe PHP-bestanden.
- De nieuwe migratie is toegepast op de bestaande lokale database, zonder bestaande financiële tabellen te wijzigen.

De tests controleren budget-CRUD, unieke constraint en validatie, grenspercentages, overschrijding, actuele herberekening na uitgavewijzigingen, kopiëren over de jaargrens, geen overschrijven, gebruikersisolatie en cascade delete. Voorspellingstests controleren drie, zes en twaalf maanden, negatieve resultaten, schaarse en ontbrekende historie, centafronding, actuele en toekomstige uitgaven uitsluiten, begin- en einddatums, inactieve posten, tijdelijke aannames, reset en ongewijzigde financiële records.

De browsercontrole gebruikt uitsluitend fictieve gegevens. Gecontroleerd zijn toevoegen en bewerken van budgetten, dubbele budgetten weigeren, verwijderbevestiging annuleren, maandwisseling en kopiëren. Ook zijn de horizonknoppen, negatieve prognoses, ongeldige aannames en reset gecontroleerd. Desktop, tablet en mobiele weergaven worden gecontroleerd met beide thema's; screenshots staan in de README. De permanente applicatiedatabase bevat geen screenshotaccount.

De automatische databasecontroles gebruiken SQLite in het geheugen. Deze uitbreiding is niet opnieuw op MySQL getest. De bestaande databaseconfiguratie is behouden. Voor een andere installatie zijn `php artisan migrate --force` en `npm run build` nodig.
