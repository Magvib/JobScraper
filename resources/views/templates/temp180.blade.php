{{-- Overprint Proof --}}
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
        /* Trykprøve-papir i koldt tone */
        .proof {
            background:
                radial-gradient(ellipse 55% 35% at 90% 0%, rgba(23, 78, 106, .06), transparent 70%),
                #f5f4f0;
        }
        /* Overprint: farvelag blandes som rigtig separationsprøve */
        .op-cyan { color: #0e7490; mix-blend-mode: multiply; }
        .op-magenta { color: #be185d; mix-blend-mode: multiply; }
        .op-yellow { color: #a16207; mix-blend-mode: multiply; }
        .op-stacked { letter-spacing: -0.05em; }
        /* Passer-mærker i hjørnerne */
        .regmark {
            width: 16px; height: 16px;
            border: 1.5px solid #64748b;
            border-radius: 9999px;
            position: relative;
        }
        .regmark::before, .regmark::after {
            content: '';
            position: absolute;
            background: #64748b;
        }
        .regmark::before { left: 50%; top: -5px; bottom: -5px; width: 1px; transform: translateX(-50%); }
        .regmark::after { top: 50%; left: -5px; right: -5px; height: 1px; transform: translateY(-50%); }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page proof max-w-[210mm] mx-auto shadow-lg min-h-[297mm] font-sans text-slate-700 px-12 py-10">

        {{-- Passer-mærker i hjørnerne --}}
        <div class="regmark absolute top-4 left-4 opacity-60"></div>
        <div class="regmark absolute top-4 right-4 opacity-60"></div>
        <div class="regmark absolute bottom-4 left-4 opacity-60"></div>
        <div class="regmark absolute bottom-4 right-4 opacity-60"></div>

        {{-- Farvebar som på en tryksag --}}
        <div class="flex items-center justify-between text-[9px] font-bold uppercase tracking-[0.3em] text-slate-500">
            <span>Prøvetryk · Separate</span>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-sm bg-cyan-700 mix-blend-multiply"></span>
                <span class="w-3 h-3 rounded-sm bg-pink-700 mix-blend-multiply"></span>
                <span class="w-3 h-3 rounded-sm bg-amber-600 mix-blend-multiply"></span>
                <span class="w-3 h-3 rounded-sm bg-slate-800 mix-blend-multiply"></span>
            </div>
        </div>

        {{-- Navn som trelags overprint --}}
        <header class="relative mt-6">
            <h1 class="op-stacked text-6xl font-black leading-none select-none" aria-label="{{ $user->name }}">
                <span class="op-cyan block" style="transform: translate(3px, 3px);">{{ $user->name }}</span>
                <span class="op-magenta block" style="transform: translate(0, -0.62em);"></span>
                <span class="op-yellow block" style="transform: translate(-3px, -1.24em);"></span>
            </h1>
            <p class="relative -mt-[0.62em] text-lg font-semibold text-slate-700 tracking-wide">
                @if ($user->job_title){{ $user->job_title }}@else&nbsp;@endif
            </p>
        </header>

        {{-- Kontaktlinje under prøvestreg --}}
        <div class="mt-4 border-t-2 border-slate-800 pt-3 text-[12px] text-slate-600 flex flex-wrap gap-x-4">
            @if ($user->phone)<span>{{ $user->phone }}</span>@endif
            @if ($user->email)<span class="break-all">{{ $user->email }}</span>@endif
            @if ($user->address || $user->city)
                <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)<span>Født {{ $user->birthdate->format('d/m/Y') }}</span>@endif
            @if ($photo)<span class="sr-only">Foto findes i profilen</span>@endif
        </div>

        @if (isset($coverLetter))
            <section class="mt-9">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.4em] text-slate-800">
                    <span class="op-cyan">Ansøgning</span>
                </h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-600 max-w-[160mm]">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            {{-- Tidslinje med CMY-punkter --}}
            <section class="mt-9">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.4em] text-slate-800">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6">
                    @foreach ($jobs as $job)
                        <article class="relative pl-11 {{ ! $loop->last ? 'pb-9' : '' }}">
                            <span class="absolute left-[9px] top-3 bottom-0 w-px bg-slate-300 {{ $loop->last ? 'hidden' : '' }}"></span>
                            {{-- Farvepunkt pr. trykplade --}}
                            <span class="absolute left-0 top-1 w-5 h-5 rounded-full ring-4 ring-[#f5f4f0] {{ ['bg-cyan-700', 'bg-pink-700', 'bg-amber-600'][$loop->index % 3] }} mix-blend-multiply opacity-90"></span>
                            <p class="text-[10px] font-bold tracking-[0.25em] text-slate-500 tabular-nums uppercase">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-1 text-lg font-bold text-slate-800 leading-snug">{{ $job['title'] }}</h3>
                            <p class="text-sm font-semibold text-slate-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-slate-600 max-w-[150mm]">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            {{-- Kompetencer som separationslinjer --}}
            <section class="mt-9">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.4em] text-slate-800">Kompetencer</h2>
                <div class="mt-5 grid grid-cols-2 gap-x-10 gap-y-2.5">
                    @foreach ($skills as $skill)
                        <div class="flex items-baseline gap-2 text-[13px] text-slate-700 font-medium">
                            <span class="w-2.5 h-2.5 rounded-sm shrink-0 self-center {{ ['bg-cyan-700', 'bg-pink-700', 'bg-amber-600'][$loop->index % 3] }} mix-blend-multiply opacity-80"></span>
                            {{ $skill }}
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-9">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.4em] text-slate-800">Links</h2>
                <ul class="mt-5 space-y-1.5 text-sm text-slate-600">
                    @foreach ($links as $link)
                        <li>
                            <span class="font-semibold text-slate-800">{{ $link->name }}:</span>
                            <a href="{{ $link->url }}" class="text-cyan-800 underline decoration-pink-700/50 decoration-2 underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Underkant med densitetsmåling --}}
        <footer class="mt-11 pt-4 border-t border-slate-400 flex justify-between items-center text-[9px] font-bold uppercase tracking-[0.3em] text-slate-500">
            <span>{{ $user->name }} · Godkendt til tryk</span>
            <div class="flex items-center gap-1">
                @foreach ([100, 80, 60, 40, 20] as $step)
                    <span class="w-5 h-2.5" style="background: rgb(calc(2 - 2 * {{ $step / 100 }}), calc(4 - 4 * {{ $step / 100 }}), calc(6 - 6 * {{ $step / 100 }}));"></span>
                @endforeach
                <span class="ml-2">{{ now()->format('m.Y') }}</span>
            </div>
        </footer>
    </div>
</body>
</html>