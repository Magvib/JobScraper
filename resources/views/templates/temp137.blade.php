{{-- Sunburst Corner --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ $user->name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .cv-page { box-shadow: none !important; margin: 0 !important; max-width: 100% !important; }
        }
        .cv-page { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        /* Dæmpede solstråler fra øverste venstre hjørne */
        .sunburst {
            background: repeating-conic-gradient(
                from -8deg at 0% 0%,
                rgba(180, 140, 40, 0.14) 0deg 6deg,
                transparent 6deg 18deg
            );
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fdf9f0] shadow-lg min-h-[297mm] font-serif text-stone-900 overflow-hidden relative">

        {{-- Strålekrans bag headeren --}}
        <div class="sunburst absolute top-0 left-0 w-[420px] h-[420px] pointer-events-none"></div>

        <div class="relative px-14 py-14">
            <header class="pb-9 border-b-2 border-stone-900 flex items-start justify-between gap-8">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.5em] text-amber-700">Curriculum Vitae</p>
                    <h1 class="mt-4 text-5xl font-bold tracking-tight leading-none">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-3 text-lg italic text-stone-600">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <div class="shrink-0 border-2 border-stone-900 p-1 bg-white rotate-2 shadow-md">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover grayscale-30 sepia-20">
                    </div>
                @endif
            </header>
            <p class="mt-4 text-xs tracking-wide text-stone-500 font-sans">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
            </p>

            @if (isset($coverLetter))
                <section class="mt-9">
                    <h2 class="text-sm font-bold uppercase tracking-[0.3em] text-amber-800 flex items-center gap-4">
                        <span class="text-lg text-amber-600">✳</span> Ansøgning <span class="flex-1 h-px bg-stone-300"></span>
                    </h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-loose text-stone-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-9">
                    <h2 class="text-sm font-bold uppercase tracking-[0.3em] text-amber-800 flex items-center gap-4">
                        <span class="text-lg text-amber-600">✳</span> Erhvervserfaring &amp; uddannelse <span class="flex-1 h-px bg-stone-300"></span>
                    </h2>
                    <div class="mt-7 space-y-7">
                        @foreach ($jobs as $job)
                            <article class="flex gap-6">
                                <div class="shrink-0 text-center w-16">
                                    <span class="inline-block w-2 h-2 rotate-45 bg-amber-600 mt-1.5"></span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-[11px] font-sans font-bold tracking-widest text-stone-400 tabular-nums uppercase">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <h3 class="mt-1 text-xl font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm text-stone-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-stone-700 text-justify">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="text-sm font-bold uppercase tracking-[0.3em] text-amber-800 flex items-center gap-4">
                        <span class="text-lg text-amber-600">✳</span> Kompetencer <span class="flex-1 h-px bg-stone-300"></span>
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-medium border border-amber-700/60 text-stone-800 bg-amber-50 px-3.5 py-1.5">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-12 pt-4 border-t border-stone-300 flex justify-between text-[10px] font-sans uppercase tracking-[0.3em] text-stone-400">
                <span>{{ $user->name }}</span>
                <span>{{ now()->format('Y') }}</span>
            </footer>
        </div>
    </div>
</body>
</html>