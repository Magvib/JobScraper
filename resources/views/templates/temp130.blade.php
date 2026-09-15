{{-- Focus Frame --}}
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
        /* Hjørne-parenteser som kamerapunkter */
        .brackets { position: relative; }
        .brackets::before, .brackets::after,
        .brackets > .br::before, .brackets > .br::after {
            content: '';
            position: absolute;
            width: 14px;
            height: 14px;
            border-color: #0891b2;
            border-style: solid;
        }
        .brackets::before { top: 0; left: 0; border-width: 2px 0 0 2px; }
        .brackets::after { top: 0; right: 0; border-width: 2px 2px 0 0; }
        .brackets > .br::before { bottom: 0; left: 0; border-width: 0 0 2px 2px; }
        .brackets > .br::after { bottom: 0; right: 0; border-width: 0 2px 2px 0; }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-slate-50 shadow-lg min-h-[297mm] font-sans text-slate-800 px-14 py-12 overflow-hidden">

        {{-- Navn + foto i fokusramme --}}
        <header class="brackets flex items-center gap-8 p-8">
            <span class="br"></span>
            @if ($photo)
                <div class="brackets shrink-0 p-2">
                    <span class="br"></span>
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover grayscale-20">
                </div>
            @endif
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 uppercase">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-base font-semibold text-cyan-600 tracking-wide">{{ $user->job_title }}</p>
                @endif
                <p class="mt-4 font-mono text-xs text-slate-500 leading-relaxed">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode(' · ') }}
                </p>
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-9">
                <h2 class="flex items-center gap-3 text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900">
                    <span class="font-mono text-cyan-600">&gt;_</span> Ansøgning
                    <span class="flex-1 h-px bg-slate-300"></span>
                </h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-9">
                <h2 class="flex items-center gap-3 text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900">
                    <span class="font-mono text-cyan-600">&gt;_</span> Erhvervserfaring &amp; uddannelse
                    <span class="flex-1 h-px bg-slate-300"></span>
                </h2>
                <div class="mt-6 space-y-5">
                    @foreach ($jobs as $job)
                        <article class="bg-white border border-slate-200 px-6 py-4">
                            <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                <p class="font-mono text-xs font-bold text-cyan-700 tabular-nums whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} → {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-sm font-medium text-slate-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="flex items-center gap-3 text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900">
                    <span class="font-mono text-cyan-600">&gt;_</span> Kompetencer
                    <span class="flex-1 h-px bg-slate-300"></span>
                </h2>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="font-mono text-[13px] font-semibold text-cyan-800 bg-cyan-50 border border-cyan-200 px-3 py-1.5 rounded">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-12 font-mono text-xs text-slate-400 flex justify-between">
            <span>{{ $user->name }}</span>
            @if ($user->birthdate)
                <span>{{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </footer>
    </div>
</body>
</html>