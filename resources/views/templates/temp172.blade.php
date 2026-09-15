{{-- Nutrition Label --}}
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
        /* Ernæringsdeklarations-boks: tunge sorte skillelinjer */
        .facts { border: 3px solid #111; }
        .rule-thick { border-top: 10px solid #111; }
        .rule-thin { border-top: 1.5px solid #111; }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
    $facts = [
        'Erfaring' => collect($jobs ?? [])->count() . ' stillinger',
        'Kompetencer' => collect($skills ?? [])->count() . ' stk.',
        'Engagement' => '100%',
        'Arbejdslyst' => 'Høj',
        'Tilgængelighed' => 'Omgående',
    ];
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#faf9f5] shadow-lg min-h-[297mm] font-sans text-neutral-900 px-14 py-12 overflow-hidden">

        <header class="flex items-start justify-between gap-8 border-b-4 border-neutral-900 pb-7">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.45em] text-neutral-400">Fakta om produktet</p>
                <h1 class="mt-3 text-5xl font-black uppercase tracking-tight leading-none">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2.5 text-lg font-bold text-neutral-600">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover border-2 border-neutral-900 shrink-0">
            @endif
        </header>
        <p class="mt-5 text-sm text-neutral-500">
            {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
        </p>

        <div class="mt-8 grid grid-cols-[1fr_240px] gap-10 items-start">
            <div>
                @if (isset($coverLetter))
                    <section>
                        <h2 class="inline-block bg-neutral-900 text-white text-xs font-black uppercase tracking-[0.3em] px-4 py-2">Ansøgning</h2>
                        <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-neutral-700">
                            {!! $coverLetter->renderContext() !!}
                        </div>
                    </section>
                @elseif ($jobs)
                    <section>
                        <h2 class="inline-block bg-neutral-900 text-white text-xs font-black uppercase tracking-[0.3em] px-4 py-2">Erhvervserfaring &amp; uddannelse · ingredienser</h2>
                        <div class="mt-6 space-y-5">
                            @foreach ($jobs as $job)
                                <article class="border-b border-neutral-200 pb-4">
                                    <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                        <h3 class="font-extrabold text-lg leading-snug">{{ $job['title'] }}</h3>
                                        <p class="text-[11px] font-black tracking-widest text-neutral-400 tabular-nums uppercase whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                    </div>
                                    <p class="text-sm font-semibold text-neutral-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-neutral-600">{{ $job['description'] }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($skills)
                    <section class="mt-8">
                        <h2 class="inline-block bg-neutral-900 text-white text-xs font-black uppercase tracking-[0.3em] px-4 py-2">Kompetencer · tilsat</h2>
                        <div class="mt-5 flex flex-wrap gap-2.5">
                            @foreach ($skills as $skill)
                                <span class="text-[13px] font-bold border-2 border-neutral-900 bg-white px-3.5 py-1.5">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- Deklarations-boksen --}}
            <aside class="facts bg-white p-4">
                <p class="text-xl font-black leading-none">Ernærings-<br>deklaration</p>
                <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-500 mt-1">per ansættelse</p>
                <div class="rule-thick mt-3 pt-2 space-y-2">
                    @foreach ($facts as $label => $value)
                        <div class="flex justify-between items-baseline {{ $loop->last ? '' : 'border-b border-neutral-200 pb-2' }}">
                            <span class="text-[13px] font-bold">{{ $label }}</span>
                            <span class="text-[13px] font-black tabular-nums">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="rule-thin mt-4 pt-3">
                    <p class="text-[10px] leading-relaxed text-neutral-500">
                        Kan indeholde spor af: nysgerrighed, humor og løsningsorientering.
                        Opbevares tørt og køligt — trives bedst i fællesskab.
                    </p>
                </div>
                <div class="rule-thin mt-3 pt-2">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-400">Nettoindhold: 1 CV</p>
                </div>
            </aside>
        </div>

        <footer class="mt-12 pt-4 border-t-4 border-neutral-900 flex justify-between text-[10px] font-black uppercase tracking-[0.3em] text-neutral-400">
            <span>{{ $user->name }}</span>
            <span>Holdbarhed: {{ now()->addYears(10)->format('Y') }}</span>
        </footer>
    </div>
</body>
</html>