{{-- Duo Split --}}
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
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-xl min-h-[297mm] font-sans text-slate-800 overflow-hidden">

        {{-- Toskåret header: mørk blok + teal blok, navnet krydser sømmen --}}
        <header class="relative flex h-[62mm]">
            <div class="w-1/2 bg-slate-900"></div>
            <div class="w-1/2 bg-teal-600"></div>
            <div class="absolute inset-0 flex items-center gap-8 px-14">
                @if ($photo)
                    <div class="w-28 h-28 shrink-0 rounded-2xl bg-white p-1.5 shadow-xl rotate-3">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-xl">
                    </div>
                @endif
                <div class="text-white">
                    <h1 class="text-4xl font-extrabold tracking-tight drop-shadow-md">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-lg font-medium text-teal-200">{{ $user->job_title }}</p>
                    @endif
                </div>
            </div>
        </header>

        <div class="px-14 py-11">
            <p class="text-sm text-slate-500 border-b border-slate-200 pb-5">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
            </p>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900 flex items-center gap-3">
                        <span class="w-1 h-6 bg-teal-600"></span> Ansøgning
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900 flex items-center gap-3">
                        <span class="w-1 h-6 bg-teal-600"></span> Erhvervserfaring &amp; Uddannelse
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[120px_1fr] gap-6">
                                <div class="rounded-lg overflow-hidden border border-slate-200">
                                    <div class="bg-slate-900 text-white text-center py-1.5 text-xs font-bold tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    </div>
                                    <div class="bg-teal-50 text-teal-800 text-center py-1 text-[11px] font-semibold tabular-nums border-t border-teal-100">
                                        – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-semibold text-teal-700">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900 flex items-center gap-3">
                        <span class="w-1 h-6 bg-slate-900"></span> Kompetencer
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-slate-800 border border-slate-300 border-b-4 border-b-slate-900 px-3.5 py-1.5 rounded">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-12 pt-4 border-t border-slate-200 flex justify-between text-xs text-slate-400">
                <span>{{ $user->name }}</span>
                <span>{{ now()->format('Y') }}</span>
            </footer>
        </div>
    </div>
</body>
</html>