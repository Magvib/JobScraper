{{-- Marker Highlight --}}
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
        /* Tuscher-farvet markering bag titler */
        .marker {
            display: inline-block;
            background: linear-gradient(100deg, transparent 1%, #fde68a 2%, #fde047 98%, transparent 99%);
            padding: 2px 8px;
            margin-left: -8px;
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-slate-800 px-14 py-13">

        <header class="flex items-start justify-between gap-8 pb-8">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 leading-tight">
                    <span class="marker">{{ $user->name }}</span>
                </h1>
                @if ($user->job_title)
                    <p class="mt-2 text-base font-semibold text-slate-500">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover shrink-0 rounded-lg border-2 border-slate-900">
            @endif
        </header>
        <p class="text-sm text-slate-600 pb-6 border-b border-slate-200">
            {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
        </p>

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="text-sm font-extrabold uppercase tracking-widest text-slate-900">
                    <span class="marker">Ansøgning</span>
                </h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="text-sm font-extrabold uppercase tracking-widest text-slate-900">
                    <span class="marker">Erhvervserfaring &amp; uddannelse</span>
                </h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="grid grid-cols-[110px_1fr] gap-6">
                            <p class="text-xs font-bold text-slate-400 tabular-nums pt-1 uppercase tracking-wider text-right">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <div class="border-l-2 border-yellow-300 pl-5">
                                <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
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
                <h2 class="text-sm font-extrabold uppercase tracking-widest text-slate-900">
                    <span class="marker">Kompetencer</span>
                </h2>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-[13px] font-semibold text-slate-700 bg-yellow-50 border border-yellow-200 px-3 py-1.5 rounded-md">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-12 pt-4 border-t border-slate-200 flex justify-between text-xs text-slate-400 tracking-wide">
            <span>{{ $user->name }}</span>
            @if ($user->birthdate)
                <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </footer>
    </div>
</body>
</html>