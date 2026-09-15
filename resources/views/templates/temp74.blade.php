{{-- Honeycomb --}}
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
        .hex { clip-path: polygon(50% 0%, 93% 25%, 93% 75%, 50% 100%, 7% 75%, 7% 25%); }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fffdf5] shadow-lg min-h-[297mm] font-sans text-stone-900">

        <header class="bg-amber-400 px-12 py-10 flex items-center justify-between gap-8 relative overflow-hidden">
            <div class="absolute -right-6 -top-8 flex gap-2 opacity-30">
                <div class="hex w-16 h-16 bg-amber-600"></div>
                <div class="hex w-16 h-16 bg-amber-200 mt-8"></div>
                <div class="hex w-16 h-16 bg-amber-600"></div>
            </div>
            <div class="relative">
                <h1 class="text-4xl font-black tracking-tight text-stone-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-stone-800/80 font-semibold text-lg">{{ $user->job_title }}</p>
                @endif
                <p class="mt-3 text-sm text-stone-800/80">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </p>
            </div>
            @if ($photo)
                <div class="hex w-28 h-28 bg-amber-300 p-1.5 shrink-0 relative">
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="hex w-full h-full object-cover">
                </div>
            @endif
        </header>

        <div class="px-12 py-9">
            @if (isset($coverLetter))
                <section>
                    <h2 class="flex items-center gap-3 text-sm font-extrabold uppercase tracking-widest text-amber-600">
                        <span class="hex w-4 h-4 bg-amber-500"></span> Ansøgning
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="flex items-center gap-3 text-sm font-extrabold uppercase tracking-widest text-amber-600">
                        <span class="hex w-4 h-4 bg-amber-500"></span> Erhvervserfaring &amp; uddannelse
                    </h2>
                    <div class="mt-6 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5 items-start">
                                <div class="hex w-14 h-14 bg-amber-200 shrink-0 flex flex-col items-center justify-center text-amber-800">
                                    <span class="text-xs font-black tabular-nums">{{ \Carbon\Carbon::parse($job['startDate'])->format('y') }}</span>
                                </div>
                                <div class="flex-1 pb-4 border-b border-amber-200">
                                    <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                        <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                        <p class="text-xs font-bold text-amber-700 whitespace-nowrap tabular-nums">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                            –
                                            {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                    </div>
                                    <p class="text-sm font-semibold text-stone-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-stone-700">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="flex items-center gap-3 text-sm font-extrabold uppercase tracking-widest text-amber-600">
                        <span class="hex w-4 h-4 bg-amber-500"></span> Kompetencer
                    </h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-semibold text-stone-800 bg-amber-100 border border-amber-300 px-3.5 py-1.5 rounded">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-9 flex justify-between text-xs text-stone-500">
                <span>{{ $user->name }}</span>
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>
