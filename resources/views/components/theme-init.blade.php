<script>
    (() => {
        const key = 'geldgrip-theme';
        const system = window.matchMedia('(prefers-color-scheme: dark)');
        const valid = value => ['light', 'dark', 'system'].includes(value);
        let preference = 'system';
        try { const saved = localStorage.getItem(key); if (valid(saved)) preference = saved; } catch {}
        const apply = () => {
            const dark = preference === 'dark' || (preference === 'system' && system.matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
            document.documentElement.dataset.themePreference = preference;
            window.dispatchEvent(new CustomEvent('geldgrip:theme', { detail: { preference, dark } }));
        };
        window.GeldGripTheme = {
            get: () => preference,
            set: value => {
                if (!valid(value)) return;
                preference = value;
                try { localStorage.setItem(key, value); } catch {}
                apply();
            },
        };
        system.addEventListener('change', apply);
        window.addEventListener('storage', event => {
            if (event.key === key || event.key === null) {
                preference = valid(event.newValue) ? event.newValue : 'system';
                apply();
            }
        });
        apply();
    })();
</script>
