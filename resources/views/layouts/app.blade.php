@php
    $user = auth()->user();

    $nav = [
        ['route' => 'dashboard', 'label' => __('Home'), 'icon' => 'home'],
        ['route' => 'jobs', 'label' => __('Jobs'), 'icon' => 'briefcase'],
        ['route' => 'cv', 'label' => __('CV'), 'icon' => 'file-text'],
        ['route' => 'cover-letter', 'label' => __('Cover Letter'), 'icon' => 'file-text'],
        ['route' => 'templates', 'label' => __('Templates'), 'icon' => 'layout-template'],
    ];

    // The two editors benefit from more room on big screens; everything else stays readable at 7xl.
    $container = request()->routeIs('cv', 'cover-letter') ? 'max-w-[100rem]' : 'max-w-7xl';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head', ['title' => config('app.name', 'JobScraper')])
</head>

<body class="min-h-screen">
    <header class="sticky top-0 z-40 w-full border-b bg-background/80 backdrop-blur supports-[backdrop-filter]:bg-background/60"
        x-data="{ mobileOpen: false }">
        <div class="mx-auto flex h-14 {{ $container }} items-center gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" wire:navigate class="shrink-0">
                <x-brand />
            </a>

            {{-- Desktop navigation --}}
            <nav class="ml-4 hidden items-center gap-1 md:flex" aria-label="{{ __('Main') }}">
                @foreach ($nav as $item)
                    @php($active = request()->routeIs($item['route']))
                    <a
                        href="{{ route($item['route']) }}"
                        wire:navigate
                        @if ($active) aria-current="page" @endif
                        @class([
                            'rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                            'bg-accent text-accent-foreground' => $active,
                            'text-muted-foreground hover:bg-accent/60 hover:text-foreground' => ! $active,
                        ])
                    >{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-1">
                <x-theme-toggle />

                @if ($user)
                    <x-ui.dropdown width="w-60">
                        <x-slot:trigger>
                            <button type="button" class="flex rounded-full outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50" aria-label="{{ __('Account menu') }}">
                                <x-ui.avatar :src="$user->getImage()" :name="$user->name" size="size-8" />
                            </button>
                        </x-slot:trigger>

                        <div class="px-2 py-1.5">
                            <p class="truncate text-sm font-medium">{{ $user->name }}</p>
                            <p class="truncate text-xs text-muted-foreground">{{ $user->email }}</p>
                        </div>
                        <x-ui.separator class="my-1" />
                        <x-ui.dropdown-item :href="route('profile')" wire:navigate :active="request()->routeIs('profile')">
                            <x-ui.icon name="user" /> {{ __('Profile') }}
                        </x-ui.dropdown-item>
                        <x-ui.dropdown-item :href="route('logout')" destructive>
                            <x-ui.icon name="log-out" /> {{ __('Log out') }}
                        </x-ui.dropdown-item>
                    </x-ui.dropdown>
                @endif

                <x-ui.button variant="ghost" size="icon" class="md:hidden" x-on:click="mobileOpen = !mobileOpen" aria-label="{{ __('Toggle navigation') }}">
                    <x-ui.icon name="menu" x-show="!mobileOpen" />
                    <x-ui.icon name="x" x-show="mobileOpen" x-cloak />
                </x-ui.button>
            </div>
        </div>

        {{-- Mobile navigation --}}
        <nav x-show="mobileOpen" x-cloak x-transition.opacity class="border-t md:hidden" aria-label="{{ __('Main') }}">
            <div class="mx-auto flex {{ $container }} flex-col gap-1 p-3">
                @foreach ($nav as $item)
                    @php($active = request()->routeIs($item['route']))
                    <a
                        href="{{ route($item['route']) }}"
                        wire:navigate
                        @class([
                            'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                            'bg-accent text-accent-foreground' => $active,
                            'text-muted-foreground hover:bg-accent/60 hover:text-foreground' => ! $active,
                        ])
                    >
                        <x-ui.icon :name="$item['icon']" /> {{ $item['label'] }}
                    </a>
                @endforeach
                <a
                    href="{{ route('profile') }}"
                    wire:navigate
                    @class([
                        'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                        'bg-accent text-accent-foreground' => request()->routeIs('profile'),
                        'text-muted-foreground hover:bg-accent/60 hover:text-foreground' => ! request()->routeIs('profile'),
                    ])
                >
                    <x-ui.icon name="user" /> {{ __('Profile') }}
                </a>
            </div>
        </nav>
    </header>

    <main class="mx-auto w-full {{ $container }} px-4 py-8 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    @persist('toast')
        <x-toaster />
    @endpersist
</body>

</html>
