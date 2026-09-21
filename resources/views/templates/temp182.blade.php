{{-- Core Strata --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($coverLetter) ? 'Letter - ' : 'CV - ' }}{{ $user->name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .cv-page { box-shadow: none !important; margin: 0 !important; max-width: 100% !important; }
        }
        .cv-page { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        /* Kernerør: afmærket papir som et boringsdiagram */
        .coresheet {
            background:
                repeating-linear-gradient(90deg, transparent 0 11.8mm, rgba(120, 84, 48, .10) 11.8mm calc(11.8mm + 1px)),
                #faf6ee;
        }
        /* Sedimentlag med forskellige farver og tekstur */
        .stratum { position: relative; }
        .stratum-1 { background: linear-gradient(180deg, #d97706, #b45309); }
        .stratum-2 { background: linear-gradient(180deg, #a8a29e, #78716c); }
        .stratum-3 { background: linear-gradient(180deg, #fbbf24, #d97706); }
        .stratum-4 { background: linear-gradient(180deg, #a16207, #854d0e); }
        /* Dybdeskala på venstre side, som på et boringsdiagram */
        .depthscale {
            background:
                repeating-linear-gradient(180deg, transparent 0 7mm, rgba(68, 50, 30, .55) 7mm calc(7mm + 1px));
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
    $strata = ['stratum-1', 'stratum-2', 'stratum-3', 'stratum-4'];
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page coresheet max-w-[210mm] mx-auto shadow-lg min-h-[297mm] font-sans text-stone-700 overflow-hidden grid grid-cols-[86px_1fr]">

        {{-- Dybdeskala ned langs venstre kant --}}
        <aside class="relative bg-[#f3ecdd] border-r border-[#785430]/30">
            <div class="depthscale absolute right-3 top-16 bottom-10 w-2.5"></div>
            <div class="absolute right-7 top-16 bottom-10 flex flex-col justify-between text-[9px] font-bold tracking-widest text-stone-500 tabular-nums"
                style="writing-mode: vertical-rl; text-orientation: mixed;">
                <span>{{ now()->format('m.Y') }}</span>
                <span>OVERFLADE</span>
                <span>FUNDAMENT</span>
            </div>
        </aside>

        <div class="px-10 py-11">
            <header class="flex items-center justify-between gap-8">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.5em] text-amber-800/90">Boringsjournal</p>
                    <h1 class="mt-3 text-5xl font-extrabold tracking-tight leading-none text-stone-800">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-3 text-lg font-semibold text-amber-700">{{ $user->job_title }}</p>
                    @endif
                    <p class="mt-4 text-[13px] text-stone-500">
                        {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('   ·   ') }}
                    </p>
                </div>
                @if ($photo)
                    <div class="shrink-0 w-28 h-28 rounded-full ring-4 ring-amber-900/15 overflow-hidden shadow-md">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    </div>
                @endif
            </header>

            <div class="mt-6 h-px bg-gradient-to-r from-amber-900/50 to-transparent"></div>

            @if (isset($coverLetter))
                <section class="mt-10">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-amber-800">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-600 max-w-[155mm]">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                {{-- Hvert erhverv er et lag i kernen --}}
                <section class="mt-10">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-amber-800">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-7 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="relative pl-12">
                                {{-- Lagbånd til venstre --}}
                                <div class="absolute left-0 top-0 bottom-0 w-7 flex flex-col rounded-sm overflow-hidden ring-1 ring-stone-400/40 shadow-sm">
                                    <span class="{{ $strata[$loop->index % 4] }} flex-1"></span>
                                    <span class="h-2 bg-stone-100 border-t border-stone-300/70"></span>
                                </div>
                                <p class="text-[10px] font-bold tracking-[0.25em] text-amber-800 tabular-nums uppercase">
                                    Dybde {{ $loop->index + 1 }} · {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 text-lg font-bold text-stone-800 leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm font-semibold text-stone-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-stone-600 max-w-[150mm]">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                {{-- Kompetencer som kerner i prøvebakke --}}
                <section class="mt-10">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-amber-800">Kompetencer</h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="inline-flex items-center gap-2 text-[13px] font-semibold text-stone-700 bg-white border border-stone-300 rounded-[3px] px-3 py-1.5 shadow-sm">
                                <span class="w-1.5 h-4 rounded-[2px] {{ ['bg-amber-600', 'bg-stone-500', 'bg-yellow-500', 'bg-amber-800'][$loop->index % 4] }}"></span>
                                {{ $skill }}
                            </span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-10">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-amber-800">Links</h2>
                    <ul class="mt-5 space-y-1.5 text-sm text-stone-600">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold text-stone-800">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-amber-800 underline decoration-amber-900/40 underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-12 pt-4 border-t border-amber-900/30 flex justify-between text-[10px] font-bold uppercase tracking-[0.3em] text-stone-500">
                <span>{{ $user->name }}</span>
                <span>Kerne taget · {{ now()->format('m.Y') }}</span>
            </footer>
        </div>
    </div>
</body>
</html>