import Chart from 'chart.js/auto';

export const euro = new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' });
export const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
Chart.defaults.font.family = 'Inter Variable, Inter, sans-serif';
Chart.defaults.font.size = 11;
Chart.defaults.plugins.legend.display = false;
Chart.defaults.animation = reducedMotion ? false : { duration: 300 };
Chart.defaults.plugins.tooltip.padding = 12;

export function themedChart(element, config) {
    const chart = new Chart(element, config);
    const apply = () => {
        const dark = document.documentElement.classList.contains('dark');
        const text = dark ? '#94a3b8' : '#64748b';
        chart.options.color = text;
        chart.options.plugins.tooltip.backgroundColor = dark ? '#0b1120' : '#0f172a';
        if (chart.config.type === 'doughnut') {
            chart.data.datasets.forEach(dataset => { dataset.borderColor = dark ? '#1e293b' : '#ffffff'; });
        }
        for (const scale of Object.values(chart.options.scales || {})) {
            scale.ticks.color = text;
            if (scale.grid.display !== false) scale.grid.color = dark ? '#334155' : '#edf1f5';
        }
        chart.update('none');
    };
    window.addEventListener('geldgrip:theme', apply);
    apply();
    return chart;
}

export const moneyScales = () => ({
    x: { grid: { display: false }, border: { display: false }, ticks: { padding: 9 } },
    y: { beginAtZero: true, border: { display: false }, grid: {}, ticks: { maxTicksLimit: 5, callback: value => euro.format(value).replace(',00', '') } },
});
