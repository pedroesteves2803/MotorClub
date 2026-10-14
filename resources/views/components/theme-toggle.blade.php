<button
    type="button"
    class="mc-theme-toggle mc-theme-toggle--floating"
    data-theme-toggle
    data-theme-toggle-button
    data-theme-toggle-floating
    x-data
    @click="$flux.appearance = document.documentElement.classList.contains('dark') ? 'light' : 'dark'"
    aria-label="Alternar tema"
    aria-pressed="false"
>
    <x-theme-headlight />
    <span class="sr-only" data-theme-toggle-status>Alternar tema</span>
</button>
