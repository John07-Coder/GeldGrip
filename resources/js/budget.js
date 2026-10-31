// Monetary calculations use integer cents, matching App\Support\Money.
export function parseCents(value) {
    const normalized = String(value).trim().replace(',', '.');
    if (!/^\d{1,10}(?:\.\d{1,2})?$/.test(normalized)) return null;
    const [euros, fraction = ''] = normalized.split('.');
    return Number(euros) * 100 + Number(fraction.padEnd(2, '0'));
}

export function inputAmount(cents) {
    return `${Math.floor(cents / 100)}.${String(cents % 100).padStart(2, '0')}`;
}

export function createScenario(baseline) {
    return structuredClone(baseline);
}

export function summarizeScenario(scenario) {
    const fixed = scenario.fixed.reduce((sum, item) => sum + (item.enabled ? item.cents : 0), 0);
    const variable = scenario.variable.reduce((sum, item) => sum + item.cents, 0);
    return { income: scenario.income, fixed, variable, remaining: scenario.income - fixed - variable };
}

export function projectForecast(months, horizon, assumptions = {}) {
    let cumulative = 0;
    return months.slice(0, horizon).map(month => {
        const income = assumptions.income ?? month.income;
        const fixed = assumptions.fixed ?? month.fixed;
        const variable = assumptions.variable ?? month.variable;
        const costs = fixed + variable;
        const remaining = income - costs;
        cumulative += remaining;
        return { ...month, income, fixed, variable, costs, remaining, cumulative };
    });
}
