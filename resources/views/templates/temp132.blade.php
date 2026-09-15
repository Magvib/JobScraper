{{-- Perforation --}}
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
        /* Riflingslinje med cirkeludstansninger i siden */
        .perf {
            position: relative;
            height: 0;
            border-top: 2px dashed #cbd5e1;
            margin: 0 14px;
        }
        .perf::before, .perf::after {
            content: '';
            position: absolute;
            top: -11px;
            width: 22px;
            height: 22px;
            background: #e2e8f0;
            border-radius: 50%;
        }
        .perf::before { left: -24px; }
        .perf::after { right: -24px; }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-slate-100 shadow-xl min-h-[297mm] font-sans text-slate-800 py-10 px-10 overflow-hidden">

        {{-- Sektioner som afløselige kuponer --}}
        <div class="bg-white shadow-md min-h-[255mm] px-12 py-10 relative">
            <div class="absolute -left-2.5 top-0 bottom-0 w-5 bg-slate-100"></div>
            <div class="absolute -right-2.5 top-0 bottom-0 w-5 bg-slate-100"></div>

            <header class="pb-7 flex items-start justify-between gap-8">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-slate-400">Ref. {{ now()->format('ymd') }}</p>
                    <h1 class="mt-2.5 text-4xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-base font-semibold text-orange-600">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover shrink-0 border border-slate-300">
                @endif
            </header>
            <p class="pb-7 text-sm text-slate-500">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </p>

            <div class="perf"></div>

            @if (isset($coverLetter))
                <section class="pt-8">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="pt-8">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-5 space-y-6">
                        @foreach ($jobs as $job)
                            <article>
                                <p class="text-[11px] font-bold tracking-widest text-orange-600 tabular-nums uppercase">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
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
                <div class="mt-8 perf"></div>
                <section class="pt-8">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900">Kompetencer</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-slate-700 bg-slate-100 border border-slate-300 px-3.5 py-1.5 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="mt-10 perf"></div>
            <footer class="pt-7 flex justify-between text-xs text-slate-400 tracking-wide">
                <span>{{ $user->name }}</span>
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>