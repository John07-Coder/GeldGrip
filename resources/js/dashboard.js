import Chart from "chart.js/auto";

const element = document.getElementById("chart-data");
if (element) {
    const data = JSON.parse(element.textContent);
    const euro = new Intl.NumberFormat("nl-NL", {
        style: "currency",
        currency: "EUR",
        maximumFractionDigits: 2,
    });
    const reducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;
    Chart.defaults.font.family = "Inter Variable, Inter, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = "#94a3b8";
    Chart.defaults.plugins.legend.display = false;
    Chart.defaults.plugins.tooltip.backgroundColor = "#0f172a";
    Chart.defaults.plugins.tooltip.padding = 12;
    const donut = document.getElementById("category-chart");
    if (donut) {
        new Chart(donut, {
            type: "doughnut",
            data: {
                labels: data.categories.map((c) => c.label),
                datasets: [
                    {
                        data: data.categories.map((c) => c.cents / 100),
                        backgroundColor: data.categories.map((c) => c.color),
                        borderWidth: 4,
                        borderColor: "#ffffff",
                        hoverOffset: 5,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "80%",
                animation: reducedMotion ? false : { duration: 600 },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (item) =>
                                ` ${item.label}: ${euro.format(item.parsed)}`,
                        },
                    },
                },
            },
        });
    }
    const comparison = document.getElementById("comparison-chart");
    if (comparison) {
        new Chart(comparison, {
            type: "bar",
            data: {
                labels: ["Inkomsten", "Vaste lasten"],
                datasets: [
                    {
                        data: [data.income / 100, data.costs / 100],
                        backgroundColor: ["#10b981", "#c7d2fe"],
                        hoverBackgroundColor: ["#059669", "#a5b4fc"],
                        borderRadius: 6,
                        maxBarThickness: 65,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: reducedMotion ? false : { duration: 600 },
                layout: { padding: { top: 12 } },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: "#64748b", padding: 9 },
                    },
                    y: {
                        beginAtZero: true,
                        suggestedMax:
                            data.income || data.costs ? undefined : 100,
                        border: { display: false, dash: [4, 4] },
                        grid: { color: "#edf1f5" },
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            callback: (value) =>
                                euro.format(value).replace(",00", ""),
                        },
                    },
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (item) => ` ${euro.format(item.parsed.y)}`,
                        },
                    },
                },
            },
        });
    }
}
