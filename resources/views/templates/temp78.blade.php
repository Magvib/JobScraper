{{-- Fern Botanical --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fbfdf9] shadow-md min-h-[297mm] font-serif text-[#2e4023] relative overflow-hidden">

        {{-- Botanisk hjørne-dekoration --}}
        <svg class="absolute top-0 left-0 w-28 h-28 text-lime-800/25" viewBox="0 0 100 100" fill="currentColor">
            <path d="M0 0 Q 40 10 50 50 Q 10 40 0 0 Z"></path>
            <path d="M0 0 Q 10 40 50 55 Q 15 15 0 0 Z" opacity="0.7"></path>
        </svg>

        <header class="px-14 pt-14 pb-6 text-center relative">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover mx-auto mb-4 rounded-full border-2 border-lime-800/40 p-1">
            @endif
            <h1 class="text-4xl font-bold tracking-wide">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-1.5 italic text-lime-900/70 text-lg">{{ $user->job_title }}</p>
            @endif
            <p class="mt-3 text-sm text-[#55704a] font-sans">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </p>
            <div class="mt-5 flex items-center justify-center gap-2 text-lime-800/50">
                <span class="h-px w-16 bg-lime-800/40"></span>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 Q 18 10 12 22 Q 6 10 12 2 Z"></path></svg>
                <span class="h-px w-16 bg-lime-800/40"></span>
            </div>
        </header>

        <div class="px-14 pb-12">
            @if (isset($coverLetter))
                <section class="mt-6">
                    <h2 class="text-center text-sm font-bold uppercase tracking-[0.3em] text-lime-900">❦ Ansøgning ❦</h2>
                    <div class="mt-6 space-y-4 leading-relaxed text-[15px]">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-6">
                    <h2 class="text-center text-sm font-bold uppercase tracking-[0.3em] text-lime-900">❦ Erhvervserfaring &amp; uddannelse ❦</h2>
                    <div class="mt-7 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="border-l-2 border-lime-800/30 pl-6">
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-semibold text-[#55704a] whitespace-nowrap tabular-nums font-sans">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm italic text-lime-900/70">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-[#3a4d2e]">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-center text-sm font-bold uppercase tracking-[0.3em] text-lime-900">❦ Kompetencer ❦</h2>
                    <p class="mt-4 text-center text-sm leading-loose text-[#3a4d2e]">{{ implode('  ❧  ', $skills) }}</p>
                </section>
            @endif

            @if ($user->birthdate)
                <p class="mt-9 text-center text-xs text-[#6d8560] font-sans">Fødselsdato: {{ $user->birthdate->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>
</body>
</html>
