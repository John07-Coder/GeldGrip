import "@fontsource-variable/inter";
import {
    createIcons,
    ArrowLeft,
    ArrowRight,
    ArrowUpRight,
    BadgeCheck,
    CalendarDays,
    Calculator,
    Car,
    ChartNoAxesColumnIncreasing,
    ChartNoAxesCombined,
    ChartPie,
    Check,
    ChevronDown,
    ChevronRight,
    CircleAlert,
    CircleCheck,
    Clapperboard,
    Copy,
    FlaskConical,
    HeartPulse,
    House,
    Info,
    Landmark,
    Layers,
    LayoutDashboard,
    Lightbulb,
    LockKeyhole,
    LogOut,
    Menu,
    Monitor,
    Moon,
    Pencil,
    Plus,
    ReceiptText,
    RotateCcw,
    Search,
    Settings2,
    Shapes,
    Shirt,
    ShoppingBag,
    ShoppingBasket,
    ShieldCheck,
    SlidersHorizontal,
    Sparkles,
    Sprout,
    Sun,
    Target,
    Trash2,
    TrendingDown,
    TrendingUp,
    TriangleAlert,
    Wallet,
    Utensils,
    Wifi,
    X,
} from "lucide";

createIcons({
    icons: {
        ArrowLeft,
        ArrowRight,
        ArrowUpRight,
        BadgeCheck,
        CalendarDays,
        Calculator,
        Car,
        ChartNoAxesColumnIncreasing,
        ChartNoAxesCombined,
        ChartPie,
        Check,
        ChevronDown,
        ChevronRight,
        CircleAlert,
        CircleCheck,
        Clapperboard,
        Copy,
        FlaskConical,
        HeartPulse,
        House,
        Info,
        Landmark,
        Layers,
        LayoutDashboard,
        Lightbulb,
        LockKeyhole,
        LogOut,
        Menu,
        Monitor,
        Moon,
        Pencil,
        Plus,
        ReceiptText,
        RotateCcw,
        Search,
        Settings2,
        Shapes,
        Shirt,
        ShoppingBag,
        ShoppingBasket,
        ShieldCheck,
        SlidersHorizontal,
        Sparkles,
        Sprout,
        Sun,
        Target,
        Trash2,
        TrendingDown,
        TrendingUp,
        TriangleAlert,
        Wallet,
        Utensils,
        Wifi,
        X,
    },
    attrs: { "stroke-width": 1.8 },
});

if (document.getElementById("chart-data")) import("./dashboard");
if (document.getElementById("simulator-data")) import("./simulator");
if (document.getElementById("forecast-data")) import("./forecast");

const syncTheme = () => {
    document.querySelectorAll('[data-theme-option]').forEach(input => {
        input.checked = input.value === window.GeldGripTheme.get();
    });
    const label = document.documentElement.classList.contains('dark') ? 'Lichte weergave inschakelen' : 'Donkere weergave inschakelen';
    document.querySelectorAll('[data-theme-toggle]').forEach(button => {
        button.setAttribute('aria-label', label);
        button.title = label;
    });
};
syncTheme();
window.addEventListener('geldgrip:theme', syncTheme);
document.querySelectorAll('[data-theme-toggle]').forEach(button => button.addEventListener('click', () => {
    window.GeldGripTheme.set(document.documentElement.classList.contains('dark') ? 'light' : 'dark');
}));
document.querySelectorAll('[data-theme-option]').forEach(input => input.addEventListener('change', () => {
    window.GeldGripTheme.set(input.value);
    document.getElementById('theme-status').textContent = 'Je weergavevoorkeur is toegepast.';
}));

const menuButton = document.getElementById("menu-toggle");
const backdrop = document.querySelector(".sidebar-backdrop");
const sidebar = document.getElementById("sidebar");
const mobileLayout = window.matchMedia("(max-width: 767px)");
const setMenu = (open) => {
    document.body.classList.toggle("menu-open", open);
    menuButton?.setAttribute("aria-expanded", String(open));
    if (backdrop) backdrop.hidden = !open;
    if (sidebar) sidebar.inert = mobileLayout.matches && !open;
    if (open) document.querySelector(".mobile-close")?.focus();
};
if (sidebar) sidebar.inert = mobileLayout.matches;
mobileLayout.addEventListener("change", () => setMenu(false));
menuButton?.addEventListener("click", () =>
    setMenu(!document.body.classList.contains("menu-open")),
);
document.querySelectorAll("[data-close-menu]").forEach((button) =>
    button.addEventListener("click", () => {
        setMenu(false);
        menuButton?.focus();
    }),
);
document.addEventListener("keydown", (event) => {
    if (!document.body.classList.contains("menu-open")) return;
    if (event.key === "Escape") {
        setMenu(false);
        menuButton?.focus();
    }
    if (event.key === "Tab") {
        const controls = [
            ...sidebar.querySelectorAll("a[href], button"),
        ].filter((control) => control.offsetParent !== null);
        const first = controls[0];
        const last = controls[controls.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        }
        if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }
});

const dialog = document.getElementById("confirm-dialog");
let pendingForm;
document.querySelectorAll("form[data-confirm]").forEach((form) => {
    form.addEventListener("submit", (event) => {
        if (form.dataset.confirmed === "true") return;
        event.preventDefault();
        pendingForm = form;
        document.getElementById("confirm-message").textContent =
            form.dataset.confirm;
        dialog.showModal();
        dialog.querySelector("[data-cancel-delete]").focus();
    });
});
dialog
    ?.querySelector("[data-cancel-delete]")
    .addEventListener("click", () => dialog.close());
dialog?.querySelector("[data-confirm-delete]").addEventListener("click", () => {
    if (pendingForm) {
        pendingForm.dataset.confirmed = "true";
        pendingForm.requestSubmit();
        dialog.close();
    }
});

// If JS is disabled the native month form remains usable with its submit button.
document.getElementById("month")?.addEventListener("change", (event) => {
    if (event.target.validity.valid) event.target.form.requestSubmit();
});

document.querySelectorAll('form[method="post"]').forEach(form => {
    form.addEventListener('submit', event => {
        if (event.defaultPrevented) return;
        form.setAttribute('aria-busy', 'true');
        form.querySelectorAll('button[type="submit"], button:not([type])').forEach(button => {
            button.disabled = true;
            button.dataset.originalLabel = button.textContent;
            button.textContent = 'Bezig…';
        });
    });
});
window.addEventListener('pageshow', () => {
    syncTheme();
    document.querySelectorAll('form[aria-busy]').forEach(form => {
        form.removeAttribute('aria-busy');
        form.querySelectorAll('[data-original-label]').forEach(button => {
            button.disabled = false;
            button.textContent = button.dataset.originalLabel;
        });
    });
});
