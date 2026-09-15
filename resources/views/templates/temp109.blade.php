{{-- Prism Hero --}}
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
        .prism-bg {
            background: linear-gradient(120deg, #1e1b4b 0%, #312e81 45%, #5b21b6 100%);
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-slate-50 shadow-2xl min-h-[297mm] font-sans text-slate-800 overflow-hidden">

        {{-- Gradient hero --}}
        <header class="prism-bg text-white px-12 pt-14 pb-24 relative">
            <div class="flex items-center gap-7">
                @if ($photo)
                    <div class="w-24 h-24 shrink-0 rounded-2xl bg-white/15 border border-white/25 p-1.5">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-xl">
                    </div>
                @endif
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-lg font-medium text-violet-200">{{ $user->job_title }}</p>
                    @endif
                </div>
            </div>
            <div class="mt-8 flex flex-wrap gap-x-8 gap-y-1.5 text-sm text-violet-100">
                @if ($user->phone)
                    <p class="flex items-center gap-2"><span class="text-violet-300">✆</span>{{ $user->phone }}</p>
                @endif
                <p class="flex items-center gap-2 break-all"><span class="text-violet-300">✉</span>{{ $user->email }}</p>
                @if ($user->address || $user->city)
                    <p class="flex items-center gap-2"><span class="text-violet-300">⌖</span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                @endif
            </div>
        </header>

        {{-- Indhold der trækker op over heroen --}}
        <div class="px-12 -mt-14 pb-12 relative">
            @if (isset($coverLetter))
                <section class="bg-white rounded-2xl shadow-lg border border-slate-200/70 px-8 py-7">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.25em] text-indigo-900">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="bg-white rounded-2xl shadow-lg border border-slate-200/70 px-8 py-7">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.25em] text-indigo-900">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5">
                                <div class="shrink-0 w-14 h-14 rounded-xl bg-indigo-50 text-indigo-900 flex items-center justify-center font-extrabold text-sm border border-indigo-100 tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('y') }}
                                </div>
                                <div>
                                    <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                        <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                        <p class="text-xs font-semibold tracking-wide text-slate-400 tabular-nums whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                    </div>
                                    <p class="text-sm font-medium text-indigo-600">{{ $job['company'] }}</p>
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
                <section class="mt-6 bg-white rounded-2xl shadow-lg border border-slate-200/70 px-8 py-7">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.25em] text-indigo-900">Kompetencer</h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-indigo-900 bg-gradient-to-br from-indigo-50 to-violet-100 border border-indigo-200/70 px-3.5 py-1.5 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-8 flex justify-between text-xs text-slate-400 tracking-wide">
                <span>{{ $user->name }}</span>
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>