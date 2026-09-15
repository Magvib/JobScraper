{{-- Twin Corners --}}
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
        /* Farvede hjørnetrekanter i modstående hjørner */
        .corner-tr {
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 130px 130px 0 0;
            border-color: #b45309 transparent transparent transparent;
        }
        .corner-bl {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 0 110px 110px;
            border-color: transparent transparent #b45309 transparent;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page relative max-w-[210mm] mx-auto bg-[#fffdf8] shadow-lg min-h-[297mm] font-sans text-stone-900 overflow-hidden">

        <div class="corner-tr"></div>
        <div class="corner-bl"></div>

        <div class="relative px-16 py-16">
            <header class="pl-2 pb-7 border-b border-stone-300">
                <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-base font-semibold text-amber-700">{{ $user->job_title }}</p>
                @endif
                <div class="mt-5 flex items-start justify-between gap-8">
                    <p class="font-mono text-xs text-stone-500 leading-relaxed">
                        {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode(' · ') }}
                    </p>
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-20 h-20 object-cover shrink-0 border border-stone-400">
                    @endif
                </div>
            </header>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="font-mono text-xs font-bold uppercase tracking-[0.35em] text-amber-800">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="font-mono text-xs font-bold uppercase tracking-[0.35em] text-amber-800">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-6">
                                <p class="shrink-0 w-16 text-right font-mono text-xs font-bold text-stone-400 tabular-nums pt-1.5 uppercase">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('y') }}–{{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('y') : 'nu' }}
                                </p>
                                <div class="border-l border-stone-300 pl-6">
                                    <h3 class="font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-stone-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="font-mono text-xs font-bold uppercase tracking-[0.35em] text-amber-800">Kompetencer</h2>
                    <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2">
                        @foreach ($skills as $skill)
                            <p class="font-mono text-sm font-semibold text-stone-800 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-amber-700 rotate-45 shrink-0"></span>{{ $skill }}
                            </p>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-14 text-right font-mono text-[10px] uppercase tracking-[0.3em] text-stone-400">
                @if ($user->birthdate)
                    Født {{ $user->birthdate->format('d/m/Y') }} ·
                @endif
                {{ $user->name }}
            </footer>
        </div>
    </div>
</body>
</html>