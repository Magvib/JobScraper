<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>{{ $title ?? config('app.name', 'JobScraper') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

{{-- Apply the saved (or system) theme before first paint to avoid a flash; runs once across wire:navigate. --}}
<script data-navigate-once>
    (function () {
        function stored() {
            try {
                return localStorage.getItem('theme');
            } catch (e) {
                return null;
            }
        }

        function apply() {
            var theme = stored();
            var dark = theme ? theme === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;

            document.documentElement.classList.toggle('dark', dark);
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: dark ? 'dark' : 'light' }));
        }

        window.setTheme = function (theme) {
            try {
                localStorage.setItem('theme', theme);
            } catch (e) {}

            apply();
        };

        apply();
        document.addEventListener('livewire:navigated', apply);
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
            if (! stored()) {
                apply();
            }
        });
    })();
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
