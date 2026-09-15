{{-- Diagonal Stream --}}
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
        /* Kæmpe skrå navnestrimmel bag toppen */
        .stream {
            position: absolute;
            top: 42mm;
            left: -40mm;
            white-space: nowrap;
            font-size: 120px;
            font-weight: 900;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: rgba(15, 118, 110, 0.10);
            transform: rotate(-8deg);
            pointer-events: none;
            user-select: none;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page relative max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-slate-800 overflow-hidden">

        <span class="stream">{{ $user->name }}</span>

        <div class="relative px-14 pt-16 pb-12">
            <header class="pb-9 border-b-2 border-teal-800 flex items-start justify-between gap-8">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-teal-600">Curriculum Vitae</p>
                    <h1 class="mt-4 text-5xl font-black tracking-tight text-slate-900 leading-none">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-3 text-lg font-semibold text-slate-500">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <div class="shrink-0 w-24 h-24 border-2 border-teal-800 p-1">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover grayscale">
                    </div>
                @endif
            </header>
            <p class="mt-5 text-sm text-slate-500">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </p>

            @if (isset($coverLetter))
                <section class="mt-9">
                    <h2 class="text-sm font-extrabold uppercase tracking-[0.3em] text-teal-800 flex items-center gap-3">
                        Ansøgning <span class="flex-1 h-0.5 bg-teal-800/20"></span>
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-9">
                    <h2 class="text-sm font-extrabold uppercase tracking-[0.3em] text-teal-800 flex items-center gap-3">
                        Erhvervserfaring &amp; uddannelse <span class="flex-1 h-0.5 bg-teal-800/20"></span>
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[105px_1fr] gap-6">
                                <p class="text-right text-xs font-extrabold text-teal-700 tabular-nums pt-1 uppercase tracking-wider">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>
                                    <span class="text-slate-400">– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}</span>
                                </p>
                                <div class="border-l-2 border-teal-800/30 pl-5">
                                    <h3 class="font-extrabold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
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
                <section class="mt-9">
                    <h2 class="text-sm font-extrabold uppercase tracking-[0.3em] text-teal-800 flex items-center gap-3">
                        Kompetencer <span class="flex-1 h-0.5 bg-teal-800/20"></span>
                    </h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-bold text-teal-900 bg-teal-50 border border-teal-800/20 px-3.5 py-1.5 -skew-x-6">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-14 pt-4 border-t border-slate-200 flex justify-between text-xs text-slate-400 tracking-wide">
                <span>{{ $user->name }}</span>
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>