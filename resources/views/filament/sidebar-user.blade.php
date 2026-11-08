@php
    $user = filament()->auth()->user();
    $name = $user ? filament()->getUserName($user) : '';
    $initials = collect(preg_split('/\s+/', trim($name)))
        ->filter()
        ->take(2)
        ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp

@if ($user)
    <div class="sw-account">
        <x-filament::dropdown placement="top-start" teleport>
            <x-slot name="trigger">
                <button type="button" class="sw-account-trigger" aria-label="Menu akun">
                    <span class="sw-avatar">{{ $initials }}</span>

                    <span class="sw-account-meta" x-show="$store.sidebar.isOpen">
                        <span class="sw-account-name">{{ $name }}</span>
                        <span class="sw-account-email">{{ $user->email }}</span>
                    </span>

                    <svg
                        class="sw-account-chevron"
                        x-show="$store.sidebar.isOpen"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="m7 15 5 5 5-5" />
                        <path d="m7 9 5-5 5 5" />
                    </svg>
                </button>
            </x-slot>

            <div class="sw-menu-head">
                <span class="sw-account-name">{{ $name }}</span>
                <span class="sw-account-email">{{ $user->email }}</span>
            </div>

            <x-filament::dropdown.list>
                <x-filament::dropdown.list.item
                    icon="heroicon-m-globe-alt"
                    tag="a"
                    :href="route('home')"
                    target="_blank"
                >
                    Lihat situs
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item
                    icon="heroicon-m-cog-6-tooth"
                    tag="a"
                    :href="route('profile.edit')"
                >
                    Pengaturan akun
                </x-filament::dropdown.list.item>
            </x-filament::dropdown.list>

            <x-filament::dropdown.list>
                <form method="post" action="{{ filament()->getLogoutUrl() }}">
                    @csrf

                    <x-filament::dropdown.list.item
                        icon="heroicon-m-arrow-left-on-rectangle"
                        tag="button"
                        type="submit"
                        data-test="admin-logout-button"
                    >
                        Keluar
                    </x-filament::dropdown.list.item>
                </form>
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    </div>
@endif
