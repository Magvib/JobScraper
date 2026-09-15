{{-- Summit Profile --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-slate-100 shadow-xl min-h-[297mm] font-sans text-slate-800 p-8">

        {{-- Visitkort-style headerkort --}}
        <header class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
            <div class="flex">
                <div class="w-[70mm] shrink-0 bg-gradient-to-br from-teal-800 to-teal-950 p-8 flex flex-col items-center justify-center text-white">
                    @if ($photo)
                        <div class="w-32 h-32 rounded-2xl bg-white/15 border border-white/30 p-1.5">
                            <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-xl">
                        </div>
                    @else
                        <div class="w-32 h-32 rounded-2xl bg-white/10 border border-white/30"></div>
                    @endif
                    @if ($user->job_title)
                        <p class="mt-5 text-[11px] font-bold uppercase tracking-[0.25em] text-teal-200 text-center">{{ $user->job_title }}</p>
                    @endif
                </div>
                <div class="flex-1 px-9 py-8 flex flex-col justify-center">
                    <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                    <div class="mt-4 space-y-1.5 text-sm text-slate-600">
                        @if ($user->phone)
                            <p class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-teal-50 text-teal-700 text-xs font-bold flex items-center justify-center shrink-0">T</span>{{ $user->phone }}</p>
                        @endif
                        <p class="flex items-center gap-3 break-all"><span class="w-6 h-6 rounded-full bg-teal-50 text-teal-700 text-xs font-bold flex items-center justify-center shrink-0">@</span>{{ $user->email }}</p>
                        @if ($user->address || $user->city)
                            <p class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-teal-50 text-teal-700 text-xs font-bold flex items-center justify-center shrink-0">A</span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                        @endif
                        @if ($user->birthdate)
                            <p class="flex items-center gap-3"><span class="w-6 h-6 rounded-full bg-teal-50 text-teal-700 text-xs font-bold flex items-center justify-center shrink-0">F</span>Født {{ $user->birthdate->format('d/m/Y') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <div class="mt-6 bg-white rounded-2xl shadow-md border border-slate-200 px-9 py-8">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.25em] text-teal-800 pb-2 border-b-2 border-teal-800/10">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.25em] text-teal-800 pb-2 border-b-2 border-teal-800/10">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5">
                                <div class="shrink-0 text-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 self-start">
                                    <p class="text-lg font-extrabold text-teal-800 tabular-nums leading-none">{{ \Carbon\Carbon::parse($job['startDate'])->format('Y') }}</p>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-1">
                                        – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('Y') : 'nu' }}
                                    </p>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-teal-700">{{ $job['company'] }}</p>
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
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.25em] text-teal-800 pb-2 border-b-2 border-teal-800/10">Kompetencer</h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-teal-900 bg-teal-50 border border-teal-200 px-3.5 py-1.5 rounded-lg">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</body>
</html>