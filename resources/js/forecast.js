import { parseCents, inputAmount, projectForecast } from './budget';
import { themedChart, moneyScales, euro } from './charts';

const initial = JSON.parse(document.getElementById('forecast-data').textContent);
let baseline = initial.baseline;
let horizon = initial.horizon;
const form = document.getElementById('forecast-form');
const inputs = [...document.querySelectorAll('[data-forecast-input]')];
const error = document.getElementById('forecast-error');
const status = document.getElementById('forecast-status');
const money = cents => `${cents < 0 ? '− ' : ''}${euro.format(Math.abs(cents) / 100)}`;
const chart = themedChart(document.getElementById('forecast-chart'), {
    type: 'line',
    data: { labels: [], datasets: [{ label: 'Verwacht cumulatief overschot', data: [], borderColor: '#10b981', backgroundColor: '#10b98114', fill: true, tension: 0.2, pointRadius: 3, pointHoverRadius: 6 }] },
    options: { responsive: true, maintainAspectRatio: false, scales: moneyScales(), plugins: { tooltip: { callbacks: { label: item => ` ${money(Math.round(item.parsed.y * 100))}` } } } },
});

function render() {
    const assumptions = {};
    let valid = true;
    for (const input of inputs) {
        const cents = input.value.trim() === '' ? null : parseCents(input.value);
        const invalid = input.value.trim() !== '' && cents === null;
        input.setAttribute('aria-invalid', String(invalid));
        input.setCustomValidity(invalid ? 'Gebruik nul of meer, met maximaal twee decimalen.' : '');
        if (invalid) valid = false;
        assumptions[input.dataset.forecastInput] = cents;
    }
    error.textContent = 'Gebruik nul of meer en maximaal twee decimalen. De laatste geldige voorspelling blijft staan.';
    error.hidden = valid;
    if (!valid) return;
    const rows = projectForecast(baseline.months, horizon, assumptions);
    const first = rows[0];
    const last = rows[rows.length - 1];
    for (const [id, cents] of [['income', first.income], ['costs', first.costs], ['remaining', first.remaining], ['total', last.cumulative]]) {
        document.getElementById(`forecast-${id}`).textContent = money(cents);
    }
    document.getElementById('forecast-monthly-card').classList.toggle('negative', first.remaining < 0);
    document.getElementById('forecast-total-card').classList.toggle('negative', last.cumulative < 0);
    document.getElementById('forecast-total-label').textContent = `Cumulatief na ${horizon} maanden`;
    document.getElementById('forecast-range').textContent = `${first.label} – ${last.label}`;
    document.querySelectorAll('[data-first-month]').forEach(element => { element.textContent = `In ${first.label.toLocaleLowerCase('nl-NL')}`; });
    document.querySelectorAll('[data-forecast-period]').forEach(button => {
        const selected = Number(button.dataset.forecastPeriod) === horizon;
        button.classList.toggle('selected', selected);
        button.setAttribute('aria-pressed', String(selected));
    });
    document.querySelector('.month-picker [name="horizon"]').value = String(horizon);
    const body = document.getElementById('forecast-rows');
    body.replaceChildren();
    for (const row of rows) {
        const tr = document.createElement('tr');
        const title = document.createElement('th');
        title.scope = 'row';
        title.textContent = row.label;
        tr.append(title);
        for (const [key, label] of [['income', 'Verwachte inkomsten'], ['fixed', 'Verwachte vaste lasten'], ['variable', 'Geschatte variabele uitgaven'], ['remaining', 'Verwacht maandresultaat'], ['cumulative', 'Cumulatief resultaat']]) {
            const cell = document.createElement('td');
            cell.dataset.label = label;
            cell.textContent = money(row[key]);
            cell.className = `amount-cell${row[key] < 0 ? ' text-negative' : ''}`;
            tr.append(cell);
        }
        body.append(tr);
    }
    chart.data.labels = rows.map(row => row.label);
    chart.data.datasets[0].data = rows.map(row => row.cumulative / 100);
    chart.data.datasets[0].borderColor = last.cumulative < 0 ? '#f87171' : '#10b981';
    chart.data.datasets[0].backgroundColor = last.cumulative < 0 ? '#f8717114' : '#10b98114';
    chart.update('none');
    status.textContent = `Voorspelling bijgewerkt: ${horizon} maanden, cumulatief ${money(last.cumulative)}.`;
}

inputs.forEach(input => input.addEventListener('input', render));
document.querySelectorAll('[data-forecast-period]').forEach(button => button.addEventListener('click', event => {
    event.preventDefault();
    horizon = Number(button.dataset.forecastPeriod);
    render();
}));
form.addEventListener('submit', event => { event.preventDefault(); render(); });

document.getElementById('reset-forecast').addEventListener('click', async event => {
    const button = event.currentTarget;
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    try {
        const url = new URL(form.action);
        url.searchParams.set('month', baseline.reference);
        url.searchParams.set('horizon', String(horizon));
        const response = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
        if (!response.ok) throw new Error('reset');
        const data = await response.json();
        if (!Array.isArray(data.baseline?.months) || data.baseline.months.length !== 12) throw new Error('reset');
        baseline = data.baseline;
        for (const input of inputs) {
            input.value = '';
            const field = input.dataset.forecastInput;
            const cents = baseline.months[0][field];
            input.placeholder = inputAmount(cents).replace('.', ',');
            document.getElementById(`forecast-${field}-help`).textContent = `Standaard: ${money(cents)}${field === 'variable' ? ' per maand' : ' in de eerste maand'}.`;
        }
        const history = baseline.history;
        document.getElementById('forecast-history').textContent = history.length
            ? `Schatting op basis van ${history.length} volledige ${history.length === 1 ? 'maand' : 'maanden'} met uitgaven: ${history.map(period => period.label).reverse().join(', ')}. Maanden zonder geregistreerde uitgaven zijn niet als nulmaand aangenomen.`
            : 'Nog geen bruikbare uitgavenhistorie. De standaard schatting is € 0. Vul hierboven zelf je verwachte variabele maanduitgaven in.';
        render();
        status.textContent = 'Standaardwaarden hersteld naar je actuele gegevens.';
    } catch {
        error.textContent = 'Herstellen is niet gelukt. Controleer je verbinding of log opnieuw in en probeer het nogmaals.';
        error.hidden = false;
    } finally {
        button.disabled = false;
        button.removeAttribute('aria-busy');
    }
});
render();
