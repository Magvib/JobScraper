{{-- Column Press --}}
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
        /* Avisset i to spalter */
        .press-columns {
            columns: 2;
            column-gap: 42px;
            column-rule: 1px solid #d6d3d1;
        }
        .press-columns > * { break-inside: avoid; }
        /* Begyndelsesbogstav */}
        .dropcap::first-letter {
            float: left;
            font-size: 3.1em;
            line-height: 0.8;
            padding-right: 0.08em;
            padding-top: 0.05em;
            font-weight: 700;
            color: #9f1239;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fbfaf8] shadow-lg min-h-[297mm] font-serif text-stone-900 px-14 py-12 overflow-hidden">

        {{-- Avis-masthead --}}
        <header class="text-center border-b-2 border-double border-stone-900 pb-7">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-20 h-20 object-cover rounded-full mx-auto border border-stone-400">
            @endif
            <h1 class="mt-3 text-5xl font-bold tracking-tight">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-1.5 text-base italic text-stone-600">{{ $user->job_title }}</p>
            @endif
            <div class="mt-5 flex items-center justify-center gap-4 font-sans text-[10px] uppercase tracking-[0.25em] text-stone-500">
                <span class="flex-1 border-t border-stone-300"></span>
                <span>
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </span>
                <span class="flex-1 border-t border-stone-300"></span>
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="font-sans text-xs font-extrabold uppercase tracking-[0.35em] text-rose-800 text-center">Ansøgning</h2>
                <div class="dropcap mt-6 press-columns text-[15px] leading-loose text-stone-800 space-y-4">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="font-sans text-xs font-extrabold uppercase tracking-[0.35em] text-rose-800 text-center">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 press-columns text-[15px] leading-relaxed text-stone-800 space-y-5">
                    @foreach ($jobs as $job)
                        <article>
                            <p class="font-sans text-[10px] font-bold tracking-widest text-stone-400 tabular-nums uppercase">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                            <p class="text-sm italic text-stone-600">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-1.5 leading-relaxed text-stone-700 text-justify">{{ $job['description'] }}</p>
                            @endif
                            <div class="border-b border-stone-300 mt-3"></div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-8 pt-6 border-t border-stone-300">
                <h2 class="font-sans text-xs font-extrabold uppercase tracking-[0.35em] text-rose-800 text-center">Kompetencer</h2>
                <p class="mt-4 text-center text-sm leading-loose font-medium">
                    {{ implode('   ·   ', $skills) }}
                </p>
            </section>
        @endif

        <footer class="mt-10 pt-4 border-t-2 border-double border-stone-900 text-center font-sans text-[10px] uppercase tracking-[0.3em] text-stone-400">
            @if ($user->birthdate)
                Født {{ $user->birthdate->format('d/m/Y') }}  ·
            @endif
            {{ $user->name }} — {{ now()->format('Y') }}
        </footer>
    </div>
</body>
</html>