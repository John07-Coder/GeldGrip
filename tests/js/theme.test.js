import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const source = readFileSync(new URL('../../resources/views/components/theme-init.blade.php', import.meta.url), 'utf8').replace(/<\/?script>/g, '');
function browser({ saved = null, dark = false, unavailable = false } = {}) {
    const storage = new Map(saved ? [['geldgrip-theme', saved]] : []);
    const listeners = {};
    const system = { matches: dark, addEventListener: (event, handler) => { listeners.system = handler; } };
    const classes = new Set();
    const document = { documentElement: { style: {}, dataset: {}, classList: { toggle: (name, on) => on ? classes.add(name) : classes.delete(name) } } };
    const window = { matchMedia: () => system, addEventListener: (event, handler) => { listeners[event] = handler; }, dispatchEvent: () => {} };
    const localStorage = { getItem: key => { if (unavailable) throw Error(); return storage.get(key); }, setItem: (key, value) => { if (unavailable) throw Error(); storage.set(key, value); } };
    runInNewContext(source, { window, document, localStorage, CustomEvent: class {} });
    return { window, document, system, classes, storage, listeners };
}
test('theme persists across reloads and initializes before rendering', () => {
    const first = browser();
    first.window.GeldGripTheme.set('dark');
    assert.equal(first.storage.get('geldgrip-theme'), 'dark');
    const reloaded = browser({ saved: first.storage.get('geldgrip-theme') });
    assert.ok(reloaded.classes.has('dark'));
    assert.equal(reloaded.document.documentElement.style.colorScheme, 'dark');
    reloaded.window.GeldGripTheme.set('light');
    assert.ok(!reloaded.classes.has('dark'));
});
test('system preference tracks device changes and explicit preferences override it', () => {
    const page = browser({ dark: true });
    assert.ok(page.classes.has('dark'));
    page.system.matches = false;
    page.listeners.system();
    assert.ok(!page.classes.has('dark'));
    page.window.GeldGripTheme.set('light');
    page.system.matches = true;
    page.listeners.system();
    assert.ok(!page.classes.has('dark'));
    page.window.GeldGripTheme.set('system');
    assert.ok(page.classes.has('dark'));
});
test('invalid or blocked storage does not break theme and other tabs synchronize', () => {
    assert.equal(browser({ saved: 'invalid' }).window.GeldGripTheme.get(), 'system');
    const page = browser({ unavailable: true });
    page.window.GeldGripTheme.set('dark');
    assert.ok(page.classes.has('dark'));
    page.listeners.storage({ key: 'geldgrip-theme', newValue: 'light' });
    assert.ok(!page.classes.has('dark'));
    page.window.GeldGripTheme.set('invalid');
    assert.equal(page.window.GeldGripTheme.get(), 'light');
});
