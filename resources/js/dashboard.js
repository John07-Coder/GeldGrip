import { themedChart, moneyScales, euro } from './charts';

const element = document.getElementById('chart-data');
if (element) {
    const data = JSON.parse(element.textContent);
    const donut = document.getElementById('category-chart');
    if (donut) themedChart(donut, {
        type: 'doughnut',
        data: { labels: data.categories.map(c => c.label), datasets: [{ data: data.categories.map(c => c.cents / 100), backgroundColor: data.categories.map(c => c.color), borderWidth: 4, borderRadius: 4, hoverOffset: 5 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '80%', plugins: { tooltip: { callbacks: { label: item => ` ${item.label}: ${euro.format(item.parsed)}` } } } },
    });
    const comparison = document.getElementById('comparison-chart');
    if (comparison) themedChart(comparison, {
        type: 'bar',
        data: { labels: ['Inkomsten', 'Vaste lasten', 'Uitgaven'], datasets: [{ data: [data.income / 100, data.costs / 100, data.variableCosts / 100], backgroundColor: ['#10b981', '#a78bfa', '#f59e0b'], borderRadius: 6, maxBarThickness: 65 }] },
        options: { responsive: true, maintainAspectRatio: false, scales: moneyScales(), plugins: { tooltip: { callbacks: { label: item => ` ${euro.format(item.parsed.y)}` } } } },
    });
    const trend = document.getElementById('trend-chart');
    if (trend) themedChart(trend, {
        type: 'bar',
        data: { labels: data.trend.map(period => period.label), datasets: [{ label: 'Uitgaven', data: data.trend.map(period => period.cents / 100), backgroundColor: '#10b981', borderRadius: 5, maxBarThickness: 40 }] },
        options: { responsive: true, maintainAspectRatio: false, scales: moneyScales(), plugins: { tooltip: { callbacks: { label: item => ` ${euro.format(item.parsed.y)}` } } } },
    });
}
