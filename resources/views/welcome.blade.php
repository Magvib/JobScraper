<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => config('app.name', 'JobScraper').' — AI-Powered Job Search'])
</head>
<body class="min-h-screen">

    {{-- Navbar --}}
    <header class="fixed inset-x-0 top-0 z-50 border-b bg-background/80 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="/"><x-brand /></a>
            <div class="flex items-center gap-2">
                <x-theme-toggle />
                @auth
                    <x-ui.button size="sm" :href="route('dashboard')">{{ __('Dashboard') }} <x-ui.icon name="arrow-right" /></x-ui.button>
                @else
                    <x-ui.button size="sm" href="/auth/redirect">
                        <x-ui.icon name="github" /> {{ __('Sign in with GitHub') }}
                    </x-ui.button>
                @endauth
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative overflow-hidden pb-20 pt-32 lg:pt-40">
        <div class="pointer-events-none absolute inset-0 -z-10 [background-image:radial-gradient(var(--border)_1px,transparent_1px)] [background-size:24px_24px] [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]"></div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-center gap-16 lg:grid-cols-2">
                <div class="text-center lg:text-left">
                    <x-ui.badge variant="outline" class="mb-6 gap-2 px-3 py-1 text-sm">
                        <span class="size-1.5 animate-pulse rounded-full bg-success"></span>
                        {{ __('150+ CV & Cover Letter Templates') }}
                    </x-ui.badge>

                    <h1 class="mb-6 text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl xl:text-6xl">
                        {{ __('Land your dream job faster with AI') }}
                    </h1>

                    <p class="mx-auto mb-10 max-w-xl text-lg leading-relaxed text-muted-foreground lg:mx-0">
                        {{ __('Build your CV and cover letter right here with simple forms, choose from 150+ all-in-one templates, and discover perfectly matched jobs — all in one place.') }}
                    </p>

                    <div class="flex flex-col items-center justify-center gap-3 sm:flex-row lg:justify-start">
                        <x-ui.button size="lg" href="/auth/redirect">
                            <x-ui.icon name="github" size="size-5" /> {{ __('Get started for free') }}
                        </x-ui.button>
                        <x-ui.button size="lg" variant="ghost" href="#templates">
                            {{ __('Browse templates') }} <x-ui.icon name="arrow-down" />
                        </x-ui.button>
                    </div>
                </div>

                {{-- Template fan --}}
                @if (count($showcaseTemplates) >= 3)
                    <div class="relative hidden h-[29rem] items-start justify-center lg:flex">
                        <div class="tpl-thumb absolute left-0 top-12 -rotate-[8deg] opacity-80" style="--s:.301">
                            <iframe src="{{ route('preview.template', $showcaseTemplates[0]['slug']) }}" loading="lazy" scrolling="no" title="{{ $showcaseTemplates[0]['name'] }}"></iframe>
                        </div>
                        <div class="tpl-thumb absolute right-0 top-16 rotate-[8deg] opacity-80" style="--s:.301">
                            <iframe src="{{ route('preview.template', $showcaseTemplates[1]['slug']) }}" loading="lazy" scrolling="no" title="{{ $showcaseTemplates[1]['name'] }}"></iframe>
                        </div>
                        <div class="absolute top-0 z-10">
                            <div class="tpl-thumb" style="--s:.378">
                                <iframe src="{{ route('preview.template', $showcaseTemplates[2]['slug']) }}" loading="lazy" scrolling="no" title="{{ $showcaseTemplates[2]['name'] }}"></iframe>
                            </div>
                            <x-ui.badge class="absolute -right-3 -top-3 gap-1.5 shadow-lg">
                                <x-ui.icon name="check" /> {{ __('CV + Cover Letter') }}
                            </x-ui.badge>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Stats --}}
            <dl class="mt-16 grid grid-cols-1 gap-8 border-t pt-8 text-center sm:grid-cols-3 lg:text-left">
                <div>
                    <dt class="text-sm text-muted-foreground">{{ __('Resume Analysis') }}</dt>
                    <dd class="text-3xl font-semibold tracking-tight">AI</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">{{ __('CV & Cover Letter Templates') }}</dt>
                    <dd class="text-3xl font-semibold tracking-tight">150+</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">{{ __('Downloads') }}</dt>
                    <dd class="text-3xl font-semibold tracking-tight">{{ __('1-Click') }}</dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="border-y bg-muted/40 py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-14 text-center">
                <x-ui.badge variant="secondary" class="mb-4">{{ __('Simple process') }}</x-ui.badge>
                <h2 class="mb-3 text-3xl font-bold tracking-tight sm:text-4xl">{{ __('How it works') }}</h2>
                <p class="mx-auto max-w-xl text-muted-foreground">{{ __('Four simple steps to your next great opportunity.') }}</p>
            </div>

            @php
                $steps = [
                    ['icon' => 'file-text', 'title' => __('Build your CV'), 'text' => __('No uploads needed — create your CV right here with our simple forms, then pick from 150+ templates that combine your CV and cover letter into one polished document.')],
                    ['icon' => 'sparkles', 'title' => __('AI improves it'), 'text' => __('Our AI analyzes your resume against thousands of job descriptions and suggests targeted improvements to boost your chances.')],
                    ['icon' => 'search', 'title' => __('Discover matches'), 'text' => __('JobScraper finds jobs that truly match your skills, experience, and preferences from across the web — ranked by fit.')],
                    ['icon' => 'download', 'title' => __('Download in one click'), 'text' => __('Download your CV and cover letter — bundled in the template of your choice — with a single click, ready to attach to any application in seconds.')],
                ];
            @endphp

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($steps as $i => $step)
                    <x-ui.card size="sm" class="transition-colors hover:border-ring/50">
                        <x-ui.card-content class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex size-10 items-center justify-center rounded-lg bg-muted">
                                    <x-ui.icon :name="$step['icon']" size="size-5" />
                                </div>
                                <span class="text-xs font-medium uppercase tracking-widest text-muted-foreground">{{ __('Step :n', ['n' => $i + 1]) }}</span>
                            </div>
                            <h3 class="font-semibold">{{ $step['title'] }}</h3>
                            <p class="text-sm leading-relaxed text-muted-foreground">{{ $step['text'] }}</p>
                        </x-ui.card-content>
                    </x-ui.card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Template showcase --}}
    <section id="templates" class="py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-14 text-center">
                <x-ui.badge variant="secondary" class="mb-4">{{ __('Template gallery') }}</x-ui.badge>
                <h2 class="mb-3 text-3xl font-bold tracking-tight sm:text-4xl">{{ __('One document. Both parts. 150+ ways to shine.') }}</h2>
                <p class="mx-auto max-w-2xl text-muted-foreground">
                    {{ __('Every template combines your CV and cover letter into a single polished document. Here are :count of our favorites — refreshed on every visit.', ['count' => count($showcaseTemplates)]) }}
                </p>
            </div>

            <div class="grid justify-center gap-6" style="grid-template-columns: repeat(auto-fill, 240px)">
                @foreach ($showcaseTemplates as $template)
                    <a href="{{ route('preview.template', $template['slug']) }}" target="_blank" rel="noopener"
                        class="group overflow-hidden rounded-xl border bg-card shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-ring/50 hover:shadow-lg">
                        <div class="p-3 pb-0">
                            {{-- 240px column - 2x12px padding = 216px content box; 216/794 = .272 --}}
                            <div class="tpl-thumb mx-auto" style="--s:.2715">
                                <iframe src="{{ route('preview.template', $template['slug']) }}" loading="lazy" scrolling="no" title="{{ $template['name'] }}"></iframe>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-2 px-4 py-3">
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-medium leading-tight">{{ $template['name'] }}</h3>
                                <p class="text-xs text-muted-foreground">#{{ $template['number'] }}</p>
                            </div>
                            <span class="text-xs text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100">{{ __('View') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <p class="mt-8 text-center text-sm text-muted-foreground">
                {{ __('Previews shown with sample data — your CV and cover letter slot right in.') }}
            </p>

            <div class="mt-10 text-center">
                <x-ui.button size="lg" href="/auth/redirect">
                    {{ __('Sign in to browse all 150+ templates') }} <x-ui.icon name="arrow-right" />
                </x-ui.button>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section class="border-y bg-muted/40 py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-center gap-16 lg:grid-cols-2">
                <div>
                    <x-ui.badge variant="secondary" class="mb-6">{{ __('Why JobScraper') }}</x-ui.badge>
                    <h2 class="mb-4 text-3xl font-bold leading-tight tracking-tight sm:text-4xl">{{ __('Everything you need to get hired') }}</h2>
                    <p class="mb-8 text-lg leading-relaxed text-muted-foreground">
                        {{ __('Stop wasting hours on job boards. Let our AI do the heavy lifting while you focus on preparing for interviews.') }}
                    </p>

                    @php
                        $features = [
                            [__('Smart resume tailoring'), __('AI rewrites your resume for each job, highlighting the most relevant experience and skills.')],
                            [__('Real-time job scraping'), __('Fresh listings scraped from top job boards, filtered and ranked by how well they match your profile.')],
                            [__('Application tracking'), __('Keep track of every application, follow-up, and interview in one organized dashboard.')],
                            [__('GitHub sign-in'), __('Log in instantly with your GitHub account — no passwords, no friction.')],
                        ];
                    @endphp

                    <ul class="flex flex-col gap-5">
                        @foreach ($features as [$title, $text])
                            <li class="flex items-start gap-4">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                    <x-ui.icon name="check" size="size-3.5" />
                                </span>
                                <div>
                                    <div class="mb-0.5 font-medium">{{ $title }}</div>
                                    <div class="text-sm text-muted-foreground">{{ $text }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Mock job match card --}}
                <x-ui.card class="shadow-xl">
                    <x-ui.card-header class="flex-row items-center justify-between">
                        <x-ui.card-title class="text-lg">{{ __('Top matches for you') }}</x-ui.card-title>
                        <x-ui.badge variant="success">{{ __('Live') }}</x-ui.badge>
                    </x-ui.card-header>
                    <x-ui.card-content class="space-y-3">
                        @foreach ([['A', 'Senior Laravel Developer', 'Acme Corp · Remote', 98], ['T', 'Full-Stack Engineer', 'TechStart · Oslo, Norway', 94], ['N', 'Backend PHP Developer', 'Nordic Labs · Hybrid', 91]] as [$initial, $title, $meta, $score])
                            <div class="flex items-center gap-4 rounded-lg border p-4">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-muted text-sm font-semibold">{{ $initial }}</div>
                                <div class="min-w-0 grow">
                                    <div class="truncate font-medium">{{ $title }}</div>
                                    <div class="text-sm text-muted-foreground">{{ $meta }}</div>
                                </div>
                                <x-score-badge :value="$score" class="shrink-0" />
                            </div>
                        @endforeach

                        <x-ui.button class="w-full" href="/auth/redirect">
                            <x-ui.icon name="github" /> {{ __('Sign in to see your matches') }}
                        </x-ui.button>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-24">
        <div class="mx-auto max-w-2xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="mb-4 text-3xl font-bold tracking-tight sm:text-4xl">{{ __('Ready to find your next role?') }}</h2>
            <p class="mb-10 text-lg text-muted-foreground">{{ __('Join now and let AI handle the hard part of your job search.') }}</p>
            <x-ui.button size="lg" href="/auth/redirect">
                <x-ui.icon name="github" size="size-5" /> {{ __("Get started with GitHub — it's free") }}
            </x-ui.button>
        </div>
    </section>

    <footer class="border-t py-8 text-center text-sm text-muted-foreground">
        <p>© {{ date('Y') }} JobScraper. {{ __('Built with Laravel & AI.') }}</p>
    </footer>

</body>
</html>
