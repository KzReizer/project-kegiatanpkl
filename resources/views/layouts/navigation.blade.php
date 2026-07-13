<nav x-data="{ open: false }" class="site-nav" data-site-nav>
    <div class="nav-inner">
        <div class="nav-left">
            <a class="brand-lockup" href="{{ route('dashboard') }}" aria-label="Dashboard Jurnal PKL">
                <span class="brand-mark">
                    <i data-lucide="notebook-tabs"></i>
                </span>
                <span class="brand-text">
                    <strong>Jurnal PKL</strong>
                    <span>Internship workspace</span>
                </span>
            </a>

            <div class="nav-links">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <i data-lucide="layout-dashboard"></i>
                    {{ __('Dashboard') }}
                </x-nav-link>
                <x-nav-link :href="route('journals.index')" :active="request()->routeIs('journals.*')">
                    <i data-lucide="book-open-text"></i>
                    {{ __('Jurnal PKL') }}
                </x-nav-link>
                <x-nav-link :href="route('attendances.index')" :active="request()->routeIs('attendances.*')">
                    <i data-lucide="check-circle"></i>
                    {{ __('Absensi') }}
                </x-nav-link>
                @if (Auth::user()->isAdmin())
                    <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.*')">
                        <i data-lucide="shield-check"></i>
                        {{ __('Admin') }}
                    </x-nav-link>
                @endif
            </div>
        </div>

        <div class="nav-actions">
            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema">
                <span data-theme-icon><i data-lucide="moon"></i></span>
            </button>

            <x-dropdown align="right" width="56">
                <x-slot name="trigger">
                    <button class="user-menu-button" type="button">
                        <span class="avatar-dot">{{ \Illuminate\Support\Str::of(Auth::user()->name)->substr(0, 1)->upper() }}</span>
                        <span>{{ Auth::user()->name }}</span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">
                        <i data-lucide="user-round"></i>
                        {{ __('Profile') }}
                    </x-dropdown-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            <i data-lucide="log-out"></i>
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>

        <div class="mobile-toggle-wrap">
            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema">
                <span data-theme-icon><i data-lucide="moon"></i></span>
            </button>

            <button @click="open = ! open" class="mobile-menu-button" type="button" aria-label="Buka navigasi">
                <i x-show="! open" data-lucide="menu"></i>
                <i x-show="open" data-lucide="x"></i>
            </button>
        </div>
    </div>

    <div class="responsive-menu" :class="{ 'is-open': open }">
        <div class="responsive-menu-inner">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <i data-lucide="layout-dashboard"></i>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('journals.index')" :active="request()->routeIs('journals.*')">
                <i data-lucide="book-open-text"></i>
                {{ __('Jurnal PKL') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('attendances.index')" :active="request()->routeIs('attendances.*')">
                <i data-lucide="check-circle"></i>
                {{ __('Absensi') }}
            </x-responsive-nav-link>
            @if (Auth::user()->isAdmin())
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.*')">
                    <i data-lucide="shield-check"></i>
                    {{ __('Admin') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="responsive-user">
            <div class="responsive-user-meta">
                <strong>{{ Auth::user()->name }}</strong>
                {{ Auth::user()->email }}
            </div>

            <div class="responsive-menu-inner">
                <x-responsive-nav-link :href="route('profile.edit')">
                    <i data-lucide="user-round"></i>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        <i data-lucide="log-out"></i>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
