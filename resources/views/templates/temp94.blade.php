{{-- Frosted Glass --}}
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
    <div class="cv-page max-w-[210mm] mx-auto shadow-xl min-h-[297mm] font-sans text-slate-800 relative overflow-hidden bg-gradient-to-br from-sky-200 via-indigo-100 to-purple-200">

        {{-- Farvede baggrunds-blobs --}}
        <div class="absolute top-20 -left-16 w-64 h-64 bg-sky-300/50 rounded-full"></div>
        <div class="absolute top-96 -right-20 w-72 h-72 bg-purple-300/40 rounded-full"></div>
        <div class="absolute bottom-20 left-24 w-48 h-48 bg-indigo-200/50 rounded-full"></div>

        <div class="relative m-8 space-y-5">
            <header class="bg-white/60 backdrop-blur-md border border-white/70 rounded-3xl shadow-lg px-9 py-8 flex items-center gap-7">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 object-cover rounded-2xl shrink-0 ring-4 ring-white/70">
                @endif
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1 text-lg text-indigo-500 font-medium">{{ $user->job_title }}</p>
                    @endif
                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
                        @if ($user->phone)
                            <span>{{ $user->phone }}</span>
                        @endif
                        <span class="break-all">{{ $user->email }}</span>
                        @if ($user->address || $user->city)
                            <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                        @endif
                        @if ($user->birthdate)
                            <span>{{ $user->birthdate->format('d/m/Y') }}</span>
                        @endif
                    </div>
                </div>
            </header>

            @if (isset($coverLetter))
                <section class="bg-white/60 backdrop-blur-md border border-white/70 rounded-3xl shadow-lg px-9 py-7">
                    <h2 class="text-xs font-extrabold uppercase tracking-widest text-indigo-400">Ansøgning</h2>
                    <div class="mt-4 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="bg-white/60 backdrop-blur-md border border-white/70 rounded-3xl shadow-lg px-9 py-7">
                    <h2 class="text-xs font-extrabold uppercase tracking-widest text-indigo-400">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-5 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="border-b border-slate-200/70 pb-5 last:border-0 last:pb-0">
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold text-sky-600 whitespace-nowrap tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-medium text-indigo-400">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="bg-white/60 backdrop-blur-md border border-white/70 rounded-3xl shadow-lg px-9 py-7">
                    <h2 class="text-xs font-extrabold uppercase tracking-widest text-indigo-400">Kompetencer</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-semibold text-indigo-700 bg-white/70 border border-indigo-200 px-4 py-1.5 rounded-full shadow-sm">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</body>
</html>
