{{-- Cinema Strip --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-slate-800 overflow-hidden">

        {{-- Bredt fotobånd i cinemascope-format --}}
        <header class="relative">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-44 object-cover object-center">
            @else
                <div class="w-full h-44 bg-gradient-to-r from-slate-800 to-slate-600"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/30 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 px-14 pb-6 text-white">
                <h1 class="text-4xl font-extrabold tracking-tight drop-shadow">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1 text-lg font-semibold text-amber-300">{{ $user->job_title }}</p>
                @endif
            </div>
        </header>
        <p class="px-14 mt-5 text-sm text-slate-500">
            {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
        </p>

        <div class="px-14 pb-12">
            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="text-sm font-extrabold uppercase tracking-[0.25em] text-slate-900 flex items-center gap-3">
                        Ansøgning <span class="flex-1 h-px bg-slate-300"></span>
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="text-sm font-extrabold uppercase tracking-[0.25em] text-slate-900 flex items-center gap-3">
                        Erhvervserfaring &amp; uddannelse <span class="flex-1 h-px bg-slate-300"></span>
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-6">
                                <div class="shrink-0 w-24 text-center">
                                    <p class="text-2xl font-black text-slate-900 tabular-nums leading-none">{{ \Carbon\Carbon::parse($job['startDate'])->format('Y') }}</p>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-amber-600 mt-1">
                                        – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('Y') : 'nu' }}
                                    </p>
                                </div>
                                <div class="flex-1 border-l border-slate-200 pl-6">
                                    <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-semibold text-amber-600">{{ $job['company'] }}</p>
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
                <section class="mt-9">
                    <h2 class="text-sm font-extrabold uppercase tracking-[0.25em] text-slate-900 flex items-center gap-3">
                        Kompetencer <span class="flex-1 h-px bg-slate-300"></span>
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-slate-800 bg-amber-50 border border-amber-200 px-3.5 py-1.5 rounded-md">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-12 pt-4 border-t border-slate-200 flex justify-between text-xs text-slate-400 tracking-wide">
                <span>{{ $user->name }}</span>
                <span>{{ now()->format('Y') }}</span>
            </footer>
        </div>
    </div>
</body>
</html>