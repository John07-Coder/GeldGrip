import { test } from 'node:test';
import assert from 'node:assert/strict';
import { createScenario, parseCents, inputAmount, summarizeScenario } from '../../resources/js/budget.js';

const baseline = { income: 250000, fixed: [{ name: 'Huur', cents: 70000, enabled: true }], variable: [{ label: 'Boodschappen', cents: 45000 }] };
test('income changes and hypothetical costs update the remaining budget in cents', () => {
    const scenario = createScenario(baseline);
    assert.equal(summarizeScenario(scenario).remaining, 135000);
    scenario.income = parseCents('2800,00');
    scenario.fixed.push({ cents: parseCents('110'), enabled: true }, { cents: parseCents('45'), enabled: true }, { cents: parseCents('100'), enabled: true });
    assert.equal(summarizeScenario(scenario).fixed, 95500);
    assert.equal(summarizeScenario(scenario).remaining, 139500);
    scenario.fixed[0].enabled = false;
    scenario.variable[0].cents = 0;
    assert.equal(summarizeScenario(scenario).remaining, 254500);
});
test('reset restores a deep copy without changing the real baseline', () => {
    let scenario = createScenario(baseline);
    scenario.fixed[0].cents = 1;
    scenario.variable[0].cents = 2;
    scenario.income = 0;
    scenario = createScenario(baseline);
    assert.deepEqual(scenario, baseline);
    assert.equal(baseline.fixed[0].cents, 70000);
    assert.notEqual(scenario.fixed[0], baseline.fixed[0]);
});
test('negative results are supported while invalid expense inputs are rejected', () => {
    assert.equal(summarizeScenario({ ...createScenario(baseline), income: 0 }).remaining, -115000);
    for (const value of ['-1', '', '1e3', '1.001', 'NaN', 'Infinity', '1.000,00', '10000000000', '1.2.3']) assert.equal(parseCents(value), null, value);
    assert.equal(parseCents('0'), 0);
    assert.equal(parseCents(' 0,01 '), 1);
    assert.equal(parseCents('9999999999.99'), 999999999999);
    assert.equal(parseCents('0.10') + parseCents('0.20'), 30);
    assert.equal(inputAmount(101), '1.01');
});
