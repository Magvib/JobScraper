{{-- Meridian Timeline --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-stone-50 shadow-lg min-h-[297mm] font-sans text-stone-800 overflow-hidden">

        {{-- Centreret header --}}
        <header class="bg-stone-900 text-white px-14 py-12 text-center relative">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 object-cover rounded-full border-4 border-amber-400 mx-auto">
            @endif
            <h1 class="mt-5 text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-1.5 text-amber-300 font-semibold uppercase tracking-[0.25em] text-sm">{{ $user->job_title }}</p>
            @endif
            <p class="mt-4 text-sm text-stone-300">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </p>
        </header>

        <div class="px-14 py-12">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-center text-xs font-extrabold uppercase tracking-[0.35em] text-stone-900">Ansøgning</h2>
                    <div class="mt-6 max-w-[150mm] mx-auto space-y-4 text-[15px] leading-relaxed text-stone-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-center text-xs font-extrabold uppercase tracking-[0.35em] text-stone-900">Erhvervserfaring &amp; uddannelse</h2>

                    {{-- Central vertikal tidslinje med skiftende sider --}}
                    <div class="relative mt-10">
                        <div class="absolute left-1/2 top-0 bottom-0 w-0.5 bg-amber-400 -translate-x-1/2"></div>
                        <div class="space-y-8">
                            @foreach ($jobs as $i => $job)
                                <article class="relative grid grid-cols-2 gap-14 items-start">
                                    <span class="absolute left-1/2 top-2 -translate-x-1/2 w-4 h-4 rounded-full bg-amber-400 border-4 border-stone-50"></span>
                                    @if ($i % 2 === 0)
                                        <div class="text-right">
                                            <h3 class="font-bold text-lg leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                            <p class="text-sm font-semibold text-amber-700">{{ $job['company'] }}</p>
                                            @if (!empty($job['description']))
                                                <p class="text-sm mt-2 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                            @endif
                                        </div>
                                        <p class="text-sm font-bold tracking-wider text-stone-500 tabular-nums">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                    @else
                                        <p class="text-sm font-bold tracking-wider text-stone-500 tabular-nums">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                        <div>
                                            <h3 class="font-bold text-lg leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                            <p class="text-sm font-semibold text-amber-700">{{ $job['company'] }}</p>
                                            @if (!empty($job['description']))
                                                <p class="text-sm mt-2 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-12">
                    <h2 class="text-center text-xs font-extrabold uppercase tracking-[0.35em] text-stone-900">Kompetencer</h2>
                    <div class="mt-5 flex flex-wrap justify-center gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-semibold bg-white border border-stone-300 text-stone-800 px-4 py-1.5 rounded-full shadow-sm">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-12 text-center text-xs text-stone-400 tracking-wide">
                @if ($user->birthdate)
                    Født {{ $user->birthdate->format('d/m/Y') }}  ·
                @endif
                {{ $user->name }}
            </footer>
        </div>
    </div>
</body>
</html>