<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="halloween">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'JobScraper') }} — AI-Powered Job Search</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* A4 at 96dpi is 794x1123px — scale each template iframe down to a thumbnail.
               Set the scale per card via the --s custom property. */
            .tpl-thumb {
                position: relative;
                overflow: hidden;
                background: #fff;
                border-radius: 8px;
                width: calc(794px * var(--s));
                height: calc(1123px * var(--s));
                box-shadow: 0 10px 30px rgb(0 0 0 / 0.25);
            }

            .tpl-thumb iframe {
                position: absolute;
                top: 0;
                left: 0;
                width: 794px;
                height: 1123px;
                border: 0;
                transform: scale(var(--s));
                transform-origin: top left;
                pointer-events: none; /* let the parent anchor handle clicks */
            }
        </style>
    </head>
    <body class="min-h-screen bg-base-100 text-base-content font-sans antialiased">

        <!-- Navbar -->
        <div class="navbar bg-base-200/80 backdrop-blur-md fixed top-0 z-50 border-b border-base-300 px-4 lg:px-8">
            <div class="navbar-start">
                <a href="/" class="flex items-center gap-2 text-xl font-bold">
                    <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-content" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                    </div>
                    <span><span class="text-primary">Job</span>Scraper</span>
                </a>
            </div>
            <div class="navbar-end gap-3">
                <a href="/auth/redirect" class="btn btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/>
                    </svg>
                    Sign in with GitHub
                </a>
            </div>
        </div>

        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-32 pb-20 lg:pt-40">
            <!-- Background glow effects -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-secondary/10 rounded-full blur-3xl"></div>
            </div>

            <div class="container mx-auto px-4 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <!-- Copy -->
                    <div class="text-center lg:text-left">
                        <div class="badge badge-primary badge-outline mb-6 gap-2 px-4 py-3 text-sm font-medium">
                            <span class="w-2 h-2 bg-primary rounded-full animate-pulse"></span>
                            150+ CV &amp; Cover Letter Templates
                        </div>

                        <h1 class="text-5xl lg:text-6xl xl:text-7xl font-bold leading-tight tracking-tight mb-6">
                            Land your <span class="text-primary">dream job</span> faster with AI
                        </h1>

                        <p class="text-xl text-base-content/60 max-w-xl mx-auto lg:mx-0 mb-10 leading-relaxed">
                            Build your CV and cover letter right here with simple forms, choose from 150+ all-in-one templates, and discover perfectly matched jobs — all in one place.
                        </p>

                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                            <a href="/auth/redirect" class="btn btn-primary btn-lg gap-3 shadow-lg shadow-primary/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/>
                                </svg>
                                Get started for free
                            </a>
                            <a href="#templates" class="btn btn-ghost btn-lg">
                                Browse templates
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 5v14M19 12l-7 7-7-7"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Template fan -->
                    @if (count($showcaseTemplates) >= 3)
                        <div class="hidden lg:flex relative items-start justify-center h-115">
                            <div class="tpl-thumb absolute top-12 left-0 rotate-[-8deg] opacity-80" style="--s:.301">
                                <iframe src="{{ route('preview.template', $showcaseTemplates[0]['slug']) }}" loading="lazy" scrolling="no"
                                    title="{{ $showcaseTemplates[0]['name'] }}"></iframe>
                            </div>
                            <div class="tpl-thumb absolute top-16 right-0 rotate-[8deg] opacity-80" style="--s:.301">
                                <iframe src="{{ route('preview.template', $showcaseTemplates[1]['slug']) }}" loading="lazy" scrolling="no"
                                    title="{{ $showcaseTemplates[1]['name'] }}"></iframe>
                            </div>
                            <div class="absolute top-0 z-10">
                                <div class="tpl-thumb" style="--s:.378">
                                    <iframe src="{{ route('preview.template', $showcaseTemplates[2]['slug']) }}" loading="lazy" scrolling="no"
                                        title="{{ $showcaseTemplates[2]['name'] }}"></iframe>
                                </div>
                                <div class="badge badge-primary gap-2 absolute -top-3 -right-3 shadow-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5"/>
                                    </svg>
                                    CV + Cover Letter
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Stats row -->
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-8 mt-16 pt-8 border-t border-base-300">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-primary">AI</div>
                        <div class="text-sm text-base-content/50 mt-1">Resume Analysis</div>
                    </div>
                    <div class="w-px h-10 bg-base-300 hidden sm:block"></div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-primary">150+</div>
                        <div class="text-sm text-base-content/50 mt-1">CV &amp; Cover Letter Templates</div>
                    </div>
                    <div class="w-px h-10 bg-base-300 hidden sm:block"></div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-primary">1-Click</div>
                        <div class="text-sm text-base-content/50 mt-1">Downloads</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section id="how-it-works" class="py-24 bg-base-200">
            <div class="container mx-auto px-4 lg:px-8">
                <div class="text-center mb-16">
                    <div class="badge badge-ghost mb-4 px-4 py-3 text-sm">Simple process</div>
                    <h2 class="text-4xl lg:text-5xl font-bold mb-4">How it works</h2>
                    <p class="text-base-content/60 text-lg max-w-xl mx-auto">Four simple steps to your next great opportunity.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Step 1 -->
                    <div class="card bg-base-100 border border-base-300 hover:border-primary/50 transition-colors duration-300">
                        <div class="card-body gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4Z"/>
                                        <path d="M8 8h8M8 12h8M8 16h5"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-base-content/40 uppercase tracking-widest">Step 1</span>
                            </div>
                            <h3 class="card-title text-lg">Build your CV</h3>
                            <p class="text-base-content/60 text-sm leading-relaxed">No uploads needed — create your CV right here with our simple forms, then pick from 150+ templates that combine your CV and cover letter into one polished document.</p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="card bg-base-100 border border-base-300 hover:border-primary/50 transition-colors duration-300">
                        <div class="card-body gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-secondary/20 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-secondary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-base-content/40 uppercase tracking-widest">Step 2</span>
                            </div>
                            <h3 class="card-title text-lg">AI improves it</h3>
                            <p class="text-base-content/60 text-sm leading-relaxed">Our AI analyzes your resume against thousands of job descriptions and suggests targeted improvements to boost your chances.</p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="card bg-base-100 border border-base-300 hover:border-primary/50 transition-colors duration-300">
                        <div class="card-body gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-accent/20 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8"/>
                                        <path d="m21 21-4.3-4.3"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-base-content/40 uppercase tracking-widest">Step 3</span>
                            </div>
                            <h3 class="card-title text-lg">Discover matches</h3>
                            <p class="text-base-content/60 text-sm leading-relaxed">JobScraper finds jobs that truly match your skills, experience, and preferences from across the web — ranked by fit.</p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="card bg-base-100 border border-primary/30 hover:border-primary/70 transition-colors duration-300 ring-1 ring-primary/10">
                        <div class="card-body gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary/30 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <path d="m7 10 5 5 5-5"/>
                                        <path d="M12 15V3"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-base-content/40 uppercase tracking-widest">Step 4</span>
                            </div>
                            <h3 class="card-title text-lg">Download in one click</h3>
                            <p class="text-base-content/60 text-sm leading-relaxed">Download your CV and cover letter — bundled in the template of your choice — with a single click, ready to attach to any application in seconds.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Template showcase -->
        <section id="templates" class="py-24 bg-base-100">
            <div class="container mx-auto px-4 lg:px-8">
                <div class="text-center mb-16">
                    <div class="badge badge-ghost mb-4 px-4 py-3 text-sm">Template gallery</div>
                    <h2 class="text-4xl lg:text-5xl font-bold mb-4">One document. Both parts. <span class="text-primary">150+ ways to shine.</span></h2>
                    <p class="text-base-content/60 text-lg max-w-2xl mx-auto">
                        Every template combines your CV and cover letter into a single polished document. Here are
                        <span class="text-base-content font-semibold">{{ count($showcaseTemplates) }}</span> of our favorites — refreshed on every visit.
                    </p>
                </div>

                <div class="grid justify-center gap-6" style="grid-template-columns: repeat(auto-fill, 240px)">
                    @foreach ($showcaseTemplates as $template)
                        <a href="{{ route('preview.template', $template['slug']) }}" target="_blank" rel="noopener"
                            class="group card bg-base-100 border border-base-300 hover:border-primary/60 shadow-md hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 overflow-hidden">
                            <div class="p-3 pb-0">
                                {{-- 240px column - 2x12px padding = 216px content box; 216/794 = .272 --}}
                                <div class="tpl-thumb mx-auto" style="--s:.2715">
                                    <iframe src="{{ route('preview.template', $template['slug']) }}" loading="lazy" scrolling="no"
                                        title="{{ $template['name'] }}"></iframe>
                                </div>
                            </div>
                            <div class="card-body py-3 px-4 flex-row items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <h3 class="card-title text-base leading-tight truncate">{{ $template['name'] }}</h3>
                                    <p class="text-xs text-base-content/50">#{{ $template['number'] }}</p>
                                </div>
                                <span class="btn btn-ghost btn-xs shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                    View
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <p class="text-center text-sm text-base-content/50 mt-8">
                    Previews shown with sample data — your CV and cover letter slot right in.
                </p>

                <div class="text-center mt-10">
                    <a href="/auth/redirect" class="btn btn-primary btn-lg gap-3 shadow-lg shadow-primary/20">
                        Sign in to browse all {{ count($showcaseTemplates) > 0 ? '150+' : '' }} templates
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- Features highlight -->
        <section class="py-24 bg-base-200">
            <div class="container mx-auto px-4 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div>
                        <div class="badge badge-ghost mb-6 px-4 py-3 text-sm">Why JobScraper</div>
                        <h2 class="text-4xl lg:text-5xl font-bold mb-6 leading-tight">Everything you need to get hired</h2>
                        <p class="text-base-content/60 text-lg mb-8 leading-relaxed">
                            Stop wasting hours on job boards. Let our AI do the heavy lifting while you focus on preparing for interviews.
                        </p>
                        <ul class="flex flex-col gap-4">
                            <li class="flex items-start gap-4">
                                <div class="w-6 h-6 rounded-full bg-primary/20 flex items-center justify-center mt-0.5 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m9 11 3 3L22 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold mb-1">Smart resume tailoring</div>
                                    <div class="text-base-content/60 text-sm">AI rewrites your resume for each job, highlighting the most relevant experience and skills.</div>
                                </div>
                            </li>
                            <li class="flex items-start gap-4">
                                <div class="w-6 h-6 rounded-full bg-primary/20 flex items-center justify-center mt-0.5 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m9 11 3 3L22 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold mb-1">Real-time job scraping</div>
                                    <div class="text-base-content/60 text-sm">Fresh listings scraped from top job boards, filtered and ranked by how well they match your profile.</div>
                                </div>
                            </li>
                            <li class="flex items-start gap-4">
                                <div class="w-6 h-6 rounded-full bg-primary/20 flex items-center justify-center mt-0.5 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m9 11 3 3L22 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold mb-1">Application tracking</div>
                                    <div class="text-base-content/60 text-sm">Keep track of every application, follow-up, and interview in one organized dashboard.</div>
                                </div>
                            </li>
                            <li class="flex items-start gap-4">
                                <div class="w-6 h-6 rounded-full bg-primary/20 flex items-center justify-center mt-0.5 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m9 11 3 3L22 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold mb-1">GitHub sign-in</div>
                                    <div class="text-base-content/60 text-sm">Log in instantly with your GitHub account — no passwords, no friction.</div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Visual card -->
                    <div class="relative">
                        <div class="absolute inset-0 bg-linear-to-br from-primary/20 to-secondary/20 rounded-3xl blur-2xl"></div>
                        <div class="relative card bg-base-100 border border-base-300 shadow-2xl">
                            <div class="card-body p-8 gap-6">
                                <!-- Mock job match card -->
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-lg">Top matches for you</span>
                                    <div class="badge badge-accent badge-sm">Live</div>
                                </div>
                                <div class="flex flex-col gap-3">
                                    <div class="flex items-center gap-4 p-4 bg-base-200 rounded-xl border border-base-300 hover:border-primary/40 transition-colors cursor-pointer">
                                        <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center text-primary font-bold text-sm shrink-0">A</div>
                                        <div class="grow min-w-0">
                                            <div class="font-semibold truncate">Senior Laravel Developer</div>
                                            <div class="text-sm text-base-content/50">Acme Corp · Remote</div>
                                        </div>
                                        <div class="badge badge-primary badge-sm shrink-0">98%</div>
                                    </div>
                                    <div class="flex items-center gap-4 p-4 bg-base-200 rounded-xl border border-base-300 hover:border-primary/40 transition-colors cursor-pointer">
                                        <div class="w-10 h-10 rounded-lg bg-secondary/20 flex items-center justify-center text-secondary font-bold text-sm shrink-0">T</div>
                                        <div class="grow min-w-0">
                                            <div class="font-semibold truncate">Full-Stack Engineer</div>
                                            <div class="text-sm text-base-content/50">TechStart · Oslo, Norway</div>
                                        </div>
                                        <div class="badge badge-primary badge-sm shrink-0">94%</div>
                                    </div>
                                    <div class="flex items-center gap-4 p-4 bg-base-200 rounded-xl border border-base-300 hover:border-primary/40 transition-colors cursor-pointer">
                                        <div class="w-10 h-10 rounded-lg bg-accent/20 flex items-center justify-center text-accent font-bold text-sm shrink-0">N</div>
                                        <div class="grow min-w-0">
                                            <div class="font-semibold truncate">Backend PHP Developer</div>
                                            <div class="text-sm text-base-content/50">Nordic Labs · Hybrid</div>
                                        </div>
                                        <div class="badge badge-primary badge-sm shrink-0">91%</div>
                                    </div>
                                </div>
                                <a href="/auth/redirect" class="btn btn-primary w-full gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/>
                                    </svg>
                                    Sign in to see your matches
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-24 bg-base-100">
            <div class="container mx-auto px-4 lg:px-8 text-center">
                <div class="max-w-2xl mx-auto">
                    <h2 class="text-4xl lg:text-5xl font-bold mb-6">Ready to find your next role?</h2>
                    <p class="text-base-content/60 text-lg mb-10">Join now and let AI handle the hard part of your job search.</p>
                    <a href="/auth/redirect" class="btn btn-primary btn-lg gap-3 shadow-xl shadow-primary/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/>
                        </svg>
                        Get started with GitHub — it's free
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer footer-center py-8 bg-base-300 text-base-content/50 text-sm">
            <aside>
                <p>© {{ date('Y') }} JobScraper. Built with Laravel &amp; AI.</p>
            </aside>
        </footer>

    </body>
</html>