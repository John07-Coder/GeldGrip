import { test } from 'node:test';
import assert from 'node:assert/strict';
import { parseCents, projectForecast } from '../../resources/js/budget.js';

const months = Array.from({ length: 12 }, (_, index) => ({ month: `month-${index}`, income: 250000, fixed: 70000, variable: 45000 }));
test('three, six and twelve month projections use exact cumulative cents', () => {
    for (const [horizon, total] of [[3, 405000], [6, 810000], [12, 1620000]]) {
        const rows = projectForecast(months, horizon);
        assert.equal(rows.length, horizon);
        assert.equal(rows.at(-1).cumulative, total);
    }
});
test('monthly scheduled amounts, manual assumptions, zero and negative results are supported', () => {
    const scheduled = structuredClone(months);
    scheduled[2].income = 0;
    assert.equal(projectForecast(scheduled, 3)[2].remaining, -115000);
    assert.equal(projectForecast(scheduled, 3, { income: parseCents('2800'), fixed: 90000, variable: 50000 }).at(-1).cumulative, 420000);
    assert.equal(projectForecast(months, 12, { income: 0, variable: 0 }).at(-1).cumulative, -840000);
});
test('clearing assumptions restores scheduled values and never changes the baseline', () => {
    const before = structuredClone(months);
    const changed = projectForecast(months, 6, { income: 1 });
    changed[0].income = 999;
    assert.deepEqual(months, before);
    assert.equal(projectForecast(months, 6, { income: null, fixed: null, variable: null }).at(-1).cumulative, 810000);
});
