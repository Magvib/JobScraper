{{-- Constellation --}}
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
        /* Nattehimmel med svag stjernebrandplet */
        .sky {
            background:
                radial-gradient(ellipse 45% 40% at 82% 18%, rgba(96, 165, 250, .18), transparent 70%),
                radial-gradient(ellipse 50% 45% at 12% 55%, rgba(148, 163, 184, .12), transparent 70%),
                linear-gradient(180deg, #0f172a, #1e293b);
        }
        .star {
            position: absolute;
            width: 3px;
            height: 3px;
            border-radius: 9999px;
            background: #e2e8f0;
        }
        .star.bright { width: 5px; height: 5px; box-shadow: 0 0 6px 1px rgba(226, 232, 240, .8); }
        /* Forbindelseslinjer mellem stjerner som konstellation */
        .link-line {
            position: absolute;
            height: 1px;
            background: rgba(148, 163, 184, .45);
            transform-origin: left center;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page sky max-w-[210mm] mx-auto shadow-xl min-h-[297mm] font-sans text-slate-200 overflow-hidden relative">

        {{-- Spredte baggrundsstjerner --}}
        <span class="star bright" style="left: 12%; top: 8%;"></span>
        <span class="star" style="left: 30%; top: 14%;"></span>
        <span class="star" style="left: 68%; top: 6%;"></span>
        <span class="star bright" style="left: 88%; top: 30%;"></span>
        <span class="star" style="left: 8%; top: 40%;"></span>
        <span class="star" style="left: 50%; top: 34%;"></span>
        <span class="star bright" style="left: 78%; top: 52%;"></span>
        <span class="star" style="left: 20%; top: 60%;"></span>

        <div class="relative px-14 pt-14 pb-10">
            <header class="flex items-end gap-9">
                <div class="flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-[0.5em] text-slate-400">Stjernebillede · CV</p>
                    <h1 class="mt-4 text-5xl font-extrabold tracking-tight text-white leading-none">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-3 text-lg font-semibold text-sky-300">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <div class="shrink-0 w-24 h-24 rounded-full ring-2 ring-sky-300/60 p-1 shadow-[0_0_24px_rgba(125,211,252,.35)]">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-full">
                    </div>
                @endif
            </header>
            <p class="mt-6 text-sm text-slate-300/80">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
            </p>
        </div>

        <div class="relative px-14 pb-12">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.4em] text-sky-300 flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-sky-300 shadow-[0_0_8px_rgba(125,211,252,.9)]"></span>Ansøgning
                    </h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-slate-300">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.4em] text-sky-300 flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-sky-300 shadow-[0_0_8px_rgba(125,211,252,.9)]"></span>Erhvervserfaring &amp; uddannelse · stjernetegnet
                    </h2>
                    <div class="mt-8 space-y-7">
                        @foreach ($jobs as $i => $job)
                            <article class="relative pl-10">
                                {{-- Stjerne + forbindelseslinje ned til næste --}}
                                <span class="absolute left-0 top-1 w-3.5 h-3.5 rounded-full bg-sky-200 shadow-[0_0_10px_rgba(186,230,253,.9)]"></span>
                                @if (! $loop->last)
                                    <span class="absolute left-[6.5px] top-6 bottom-[-1.4rem] w-px bg-gradient-to-b from-slate-500/60 to-transparent"></span>
                                @endif
                                <p class="text-[11px] font-bold tracking-widest text-slate-400 tabular-nums uppercase">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-bold text-xl leading-snug text-white">{{ $job['title'] }}</h3>
                                <p class="text-sm font-medium text-sky-300/80">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-slate-400">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.4em] text-sky-300 flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-sky-300 shadow-[0_0_8px_rgba(125,211,252,.9)]"></span>Kompetencer · lysende punkter
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-3">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-sky-100 bg-slate-800/80 border border-sky-400/30 rounded-full px-4 py-1.5 shadow-[0_0_12px_rgba(56,189,248,.15)]">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-14 pt-4 border-t border-slate-700/60 flex justify-between text-[10px] font-semibold uppercase tracking-[0.3em] text-slate-500">
                <span>{{ $user->name }}</span>
                <span>Observation · {{ now()->format('d.m.Y') }}</span>
            </footer>
        </div>
    </div>
</body>
</html>