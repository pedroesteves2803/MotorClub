<header class="mc-masthead" x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false" data-site-header>
    <div class="mc-header-inner">
        <a href="{{ route('home') }}" class="mc-wordmark" aria-label="Autoverse, página inicial"><x-brand-mark class="mc-mark" /><span>AUTOVERSE</span></a>

        <div class="mc-header-controls">
            <button type="button" class="mc-menu-toggle" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" aria-expanded="false" aria-controls="site-navigation" data-menu-toggle>Menu <span class="mc-menu-icon" aria-hidden="true"><i></i><i></i></span></button>
        </div>
        <nav id="site-navigation" aria-label="Navegação principal" class="mc-nav" :class="{ 'is-open': menuOpen }" data-site-nav>
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Início</a>
            @if(config('features.garage'))
                <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard','garage.*') || (request()->routeIs('projects.*') && request()->boolean('mine'))) aria-current="page" @endif>Garagem</a>
            @endif
            @if(config('features.shots'))<a href="{{ route('shots.index') }}">Shots</a>@endif
            @if(config('features.explore'))<a href="{{ route('explore') }}" @if(request()->routeIs('explore','community','profiles.show','vehicles.show','vehicles.slug.show') || (request()->routeIs('projects.*') && !request()->boolean('mine'))) aria-current="page" @endif>Explorar</a>@endif
            @auth
                <a href="{{ route('profiles.edit') }}" @if(request()->routeIs('profiles.*','dashboard','garage.*','quotes.*','workshops.edit','shots.manage','shots.profile','settings.*')) aria-current="page" @endif>Perfil</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('filament.admin.pages.dashboard') }}" @if(request()->is('admin*')) aria-current="page" @endif>Administração</a>
                @endif
            @else
                <a href="{{ route('login') }}" class="mc-link-button">Entrar</a>
            @endauth
        </nav>
    </div>
</header>
