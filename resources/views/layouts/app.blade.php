<!DOCTYPE html>
<html lang="en" data-theme="halloween">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="navbar bg-base-100 shadow-sm">
        <div class="navbar-start">
            <div class="dropdown">
                <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
                    </svg>
                </div>
                <ul tabindex="-1" class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
                    <li><a wire:navigate href="{{ route('dashboard') }}" wire:current="bg-primary">Home</a></li>
                    <li><a wire:navigate href="{{ route('profile') }}" wire:current="bg-primary">Profile</a></li>
                    <li><a wire:navigate href="{{ route('cv') }}" wire:current="bg-primary">CV</a></li>
                    <li><a wire:navigate href="{{ route('jobs') }}" wire:current="bg-primary">Jobs</a></li>
                </ul>
            </div>
            <a class="btn btn-ghost text-xl" wire:navigate href="{{ route('home') }}"><span><span class="text-primary">Job</span>Scraper</span></a>
        </div>
        <div class="navbar-center hidden lg:flex">
            <ul class="menu menu-horizontal px-1 gap-2">
                <li><a wire:navigate href="{{ route('dashboard') }}" wire:current="bg-primary text-white">Home</a></li>
                <li><a wire:navigate href="{{ route('profile') }}" wire:current="bg-primary text-white">Profile</a></li>
                <li><a wire:navigate href="{{ route('cv') }}" wire:current="bg-primary text-white">CV</a></li>
                <li><a wire:navigate href="{{ route('jobs') }}" wire:current="bg-primary text-white">Jobs</a></li>
            </ul>
        </div>
        <div class="navbar-end">
            <a class="btn btn-error text-white" wire:navigate href="{{ route('logout') }}">Logout</a>
        </div>
    </div>

    {{ $slot }}

    @persist('toast')
    <?php
    // Alert types alert-dash alert-error alert-horizontal alert-info alert-outline alert-soft alert-success alert-vertical alert-warning
    ?>
    <div
        x-data="toastManager()"
        x-on:toast.window="add($event.detail)"
        class="toast toast-end toast-bottom"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="toast.show"
                x-transition
                :class="'alert alert-' + toast.type"
            >
                <span x-text="toast.message"></span>

                <button @click="remove(toast.id)">✕</button>
            </div>
        </template>
    </div>

    <script>
    function toastManager() {
        return {
            toasts: [],

            add(toast) {
                const id = Date.now() + Math.random();

                this.toasts.push({
                    id,
                    message: toast.message,
                    type: toast.type || 'info',
                    show: true
                });

                setTimeout(() => this.remove(id), 3000);
            },

            remove(id) {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index !== -1) {
                    this.toasts.splice(index, 1);
                }
            }
        }
    }
    </script>
    @endpersist
</body>

</html>