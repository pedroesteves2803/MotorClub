<button
    type="button"
    class="mc-theme-toggle"
    data-theme-toggle
    data-theme-toggle-button
    x-data
    @click="$flux.appearance = document.documentElement.classList.contains('dark') ? 'light' : 'dark'"
    aria-label="Alternar tema"
    aria-pressed="false"
>
    <x-theme-headlight />
    <span class="sr-only" data-theme-toggle-status>Tema escuro, farol apagado</span>
</button>
