<div class="mt-6 space-y-3 text-center">
    <div class="flex items-center gap-3 text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400">
        <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>
        <span>ou</span>
        <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>
    </div>
    <x-filament::button tag="a" :href="route('auth.google.admin')" color="gray" outlined="true" class="w-full">
        Entrar como administrador com Google
    </x-filament::button>
    <p class="text-xs text-gray-500 dark:text-gray-400">Somente contas Google autorizadas para o CMS podem entrar.</p>
</div>
