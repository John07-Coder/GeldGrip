<a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="brand" aria-label="GeldGrip home">
    <span class="brand-symbol"><x-icon name="chart-no-axes-combined" /></span>
    <span>Geld<span class="brand-accent">Grip</span><span class="brand-dot">.</span></span>
</a>
