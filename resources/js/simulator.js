import { createScenario, parseCents, inputAmount, summarizeScenario } from './budget';
import { themedChart, moneyScales, euro } from './charts';

let baseline = JSON.parse(document.getElementById('simulator-data').textContent);
let scenario = createScenario(baseline);
const income = document.getElementById('scenario-income');
const slider = document.getElementById('income-slider');
const error = document.getElementById('scenario-error');
const signed = cents => `${cents > 0 ? '+' : cents < 0 ? '− ' : ''}${euro.format(Math.abs(cents) / 100)}`;
const chart = themedChart(document.getElementById('simulator-chart'), {
    type: 'bar',
    data: { labels: ['Inkomen', 'Vaste lasten', 'Uitgaven', 'Resterend'], datasets: [
        { label: 'Huidig', data: [], backgroundColor: '#94a3b8', borderRadius: 4 },
        { label: 'Gesimuleerd', data: [], backgroundColor: '#10b981', borderRadius: 4 },
    ] },
    options: { responsive: true, maintainAspectRatio: false, scales: moneyScales(), plugins: { legend: { display: true, position: 'bottom' }, tooltip: { callbacks: { label: item => `${item.dataset.label}: ${euro.format(item.parsed.y)}` } } } },
});

function validate(input) {
    const cents = parseCents(input.value);
    const invalid = cents === null;
    input.setAttribute('aria-invalid', String(invalid));
    input.setCustomValidity(invalid ? 'Gebruik nul of meer, met maximaal twee decimalen en zonder duizendtalscheiding.' : '');
    return cents;
}

function summary(element, totals) {
    element.replaceChildren();
    for (const [key, label] of [['income', 'Netto inkomen'], ['fixed', 'Vaste lasten'], ['variable', 'Maandelijkse uitgaven'], ['remaining', 'Resterend bedrag']]) {
        const row = document.createElement('div');
        const term = document.createElement('dt');
        const value = document.createElement('dd');
        term.textContent = label;
        value.textContent = euro.format(totals[key] / 100);
        if (key === 'remaining') row.className = totals[key] < 0 ? 'summary-total text-negative' : 'summary-total text-positive';
        row.append(term, value);
        element.append(row);
    }
}

function renderResults() {
    const current = summarizeScenario(baseline);
    const simulated = summarizeScenario(scenario);
    summary(document.getElementById('current-summary'), current);
    summary(document.getElementById('simulated-summary'), simulated);
    document.getElementById('current-income').textContent = euro.format(baseline.income / 100);
    const incomeDifference = document.getElementById('income-difference');
    incomeDifference.textContent = signed(simulated.income - current.income);
    incomeDifference.className = simulated.income < current.income ? 'text-negative' : 'text-positive';
    const delta = simulated.remaining - current.remaining;
    const difference = document.getElementById('scenario-difference');
    difference.textContent = `${signed(delta)} per maand`;
    difference.parentElement.classList.toggle('is-negative', delta < 0);
    document.getElementById('scenario-verdict').textContent = delta > 0 ? 'Zoveel extra ruimte geeft dit scenario.' : delta < 0 ? 'Zoveel minder ruimte geeft dit scenario.' : 'Je resterende budget blijft gelijk.';
    chart.data.datasets[0].data = Object.values(current).map(cents => cents / 100);
    chart.data.datasets[1].data = Object.values(simulated).map(cents => cents / 100);
    chart.update('none');
}

function update() {
    error.textContent = 'Controleer de gemarkeerde bedragen. Gebruik nul of meer en maximaal twee decimalen. De laatste geldige vergelijking blijft staan.';
    const incomeCents = validate(income);
    let valid = incomeCents !== null;
    const amounts = [...document.querySelectorAll('[data-scenario-amount]')].map(input => {
        const cents = input.disabled ? scenario[input.dataset.group][Number(input.dataset.index)].cents : validate(input);
        if (cents === null) valid = false;
        return [input, cents];
    });
    error.hidden = valid;
    if (!valid) return;
    scenario.income = incomeCents;
    amounts.forEach(([input, cents]) => { scenario[input.dataset.group][Number(input.dataset.index)].cents = cents; });
    slider.max = String(Math.max(10000, Math.ceil(incomeCents / 100), Math.ceil(baseline.income / 100) * 2));
    slider.value = String(incomeCents / 100);
    renderResults();
}

function renderRows(group) {
    const target = document.getElementById(`${group}-scenario-rows`);
    target.replaceChildren();
    if (group === 'fixed' && !scenario.fixed.length) {
        const empty = document.createElement('p');
        empty.className = 'scenario-empty';
        empty.textContent = 'Geen actieve vaste lasten in deze maand. Voeg hieronder zelf een hypothetische last toe.';
        target.append(empty);
    }
    scenario[group].forEach((item, index) => {
        const row = document.createElement('div');
        row.className = 'scenario-row';
        const name = item.name || item.label;
        if (group === 'fixed') {
            const toggle = document.createElement('input');
            toggle.type = 'checkbox';
            toggle.checked = item.enabled;
            toggle.setAttribute('aria-label', `${name} meenemen`);
            toggle.addEventListener('change', () => {
                item.enabled = toggle.checked;
                row.classList.toggle('is-disabled', !item.enabled);
                row.querySelector('[data-scenario-amount]').disabled = !item.enabled;
                update();
            });
            row.append(toggle);
            row.classList.toggle('is-disabled', !item.enabled);
        }
        const label = document.createElement('label');
        const id = `scenario-${group}-${index}`;
        label.htmlFor = id;
        label.textContent = name;
        if (item.hypothetical) {
            const badge = document.createElement('small');
            badge.textContent = 'Hypothetisch';
            label.append(badge);
        }
        const input = document.createElement('input');
        input.id = id;
        input.inputMode = 'decimal';
        input.value = inputAmount(item.cents);
        input.dataset.scenarioAmount = '';
        input.dataset.group = group;
        input.dataset.index = index;
        input.setAttribute('aria-label', `${name}, maandbedrag in euro`);
        input.disabled = group === 'fixed' && !item.enabled;
        input.addEventListener('input', update);
        row.append(label, input);
        if (item.hypothetical) {
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'icon-button delete-button';
            remove.textContent = '×';
            remove.setAttribute('aria-label', `${name} uit scenario verwijderen`);
            remove.addEventListener('click', () => {
                scenario.fixed.splice(index, 1);
                renderRows('fixed');
                update();
                document.getElementById('hypothetical-name').focus();
            });
            row.append(remove);
        }
        target.append(row);
    });
}

function renderScenario() {
    income.value = inputAmount(scenario.income);
    renderRows('fixed');
    renderRows('variable');
    update();
}
income.addEventListener('input', update);
slider.addEventListener('input', () => { income.value = slider.value; update(); });
document.getElementById('add-scenario-cost').addEventListener('submit', event => {
    event.preventDefault();
    const name = document.getElementById('hypothetical-name');
    const amount = document.getElementById('hypothetical-amount');
    const cents = validate(amount);
    const message = document.getElementById('hypothetical-error');
    message.hidden = cents !== null && Boolean(name.value.trim());
    if (!message.hidden) { message.textContent = 'Vul een naam en een geldig maandbedrag van nul of meer in.'; return; }
    // Synchronize all valid edits before replacing the fixed-cost controls.
    update();
    if (!error.hidden) return;
    scenario.fixed.push({ name: name.value.trim(), cents, enabled: true, hypothetical: true });
    renderRows('fixed');
    event.target.reset();
    renderResults();
    name.focus();
});
document.getElementById('hypothetical-amount').addEventListener('input', event => validate(event.target));
document.getElementById('reset-scenario').addEventListener('click', async event => {
    const button = event.currentTarget;
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    try {
        const response = await fetch(window.location.href, { headers: { Accept: 'application/json' }, cache: 'no-store' });
        if (!response.ok) throw new Error('reset');
        const fresh = await response.json();
        if (!Number.isSafeInteger(fresh.income) || !Array.isArray(fresh.fixed) || !Array.isArray(fresh.variable)) throw new Error('reset');
        baseline = fresh;
        scenario = createScenario(baseline);
        document.getElementById('add-scenario-cost').reset();
        document.getElementById('hypothetical-error').hidden = true;
        renderScenario();
        document.getElementById('scenario-verdict').textContent = 'Scenario hersteld naar je actuele gegevens.';
    } catch {
        error.textContent = 'Resetten is niet gelukt. Controleer je verbinding of log opnieuw in en probeer het nogmaals.';
        error.hidden = false;
    } finally {
        button.disabled = false;
        button.removeAttribute('aria-busy');
    }
});
renderScenario();
