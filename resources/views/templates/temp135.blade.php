{{-- Curve Crest --}}
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
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-emerald-50/50 shadow-xl min-h-[297mm] font-sans text-slate-800 overflow-hidden">

        {{-- Dyb grøn bue som krone over siden --}}
        <header class="bg-emerald-900 text-white px-14 pt-14 pb-24 rounded-b-[3.5rem] relative">
            <div class="flex items-center gap-8">
                @if ($photo)
                    <div class="w-24 h-24 shrink-0 rounded-[2rem] bg-emerald-800 border-2 border-emerald-400/60 p-1.5">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-[1.7rem]">
                    </div>
                @endif
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-lg font-medium text-emerald-300">{{ $user->job_title }}</p>
                    @endif
                </div>
            </div>
            <p class="mt-7 text-sm text-emerald-100">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </p>
        </header>

        {{-- Indhold der glider op over buen --}}
        <div class="px-14 -mt-14 pb-12 relative">
            @if (isset($coverLetter))
                <section class="bg-white rounded-3xl shadow-lg border border-emerald-100 px-9 py-8">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.25em] text-emerald-900 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Ansøgning
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="bg-white rounded-3xl shadow-lg border border-emerald-100 px-9 py-8">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.25em] text-emerald-900 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Erhvervserfaring &amp; uddannelse
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5">
                                <div class="shrink-0 w-2 self-stretch rounded-full bg-gradient-to-b from-emerald-600 to-emerald-200"></div>
                                <div>
                                    <p class="text-[11px] font-bold tracking-widest text-emerald-600 tabular-nums uppercase">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <h3 class="mt-1 font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-slate-500">{{ $job['company'] }}</p>
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
                <section class="mt-6 bg-white rounded-3xl shadow-lg border border-emerald-100 px-9 py-8">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.25em] text-emerald-900 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Kompetencer
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-emerald-900 bg-emerald-50 border border-emerald-200 px-4 py-1.5 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-9 text-center text-xs text-slate-400 tracking-wide">
                @if ($user->birthdate)
                    Født {{ $user->birthdate->format('d/m/Y') }}  ·
                @endif
                {{ $user->name }}
            </footer>
        </div>
    </div>
</body>
</html>