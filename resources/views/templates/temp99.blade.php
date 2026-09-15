{{-- Rose Gold --}}
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
        .rose-gold { background: linear-gradient(120deg, #b76e79, #e5b5a4, #d99a8a, #c98a8a); }
        .rose-gold-text {
            background: linear-gradient(120deg, #a05a64, #c98a8a, #b76e79);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-[#4a373c]">

        <div class="rose-gold h-3"></div>

        <header class="px-14 pt-11 pb-7 text-center">
            @if ($photo)
                <div class="rose-gold w-28 h-28 rounded-full mx-auto p-1">
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-full border-4 border-white">
                </div>
            @endif
            <h1 class="rose-gold-text mt-5 text-5xl font-extrabold tracking-tight">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 text-lg text-[#9c6f76]">{{ $user->job_title }}</p>
            @endif
            <p class="mt-4 text-sm text-[#b08d94]">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </p>
        </header>

        <div class="px-14 pb-10">
            <div class="rose-gold h-px opacity-40"></div>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="rose-gold-text text-sm font-extrabold uppercase tracking-[0.3em] text-center">Ansøgning</h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="rose-gold-text text-sm font-extrabold uppercase tracking-[0.3em] text-center">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-7 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5 items-start">
                                <span class="rose-gold shrink-0 w-2 h-2 rotate-45 mt-2.5"></span>
                                <div class="flex-1">
                                    <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                        <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                        <p class="text-xs font-bold text-[#b76e79] whitespace-nowrap tabular-nums">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                            –
                                            {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                    </div>
                                    <p class="text-sm font-medium text-[#c99aa1]">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-[#6b565b]">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="rose-gold-text text-sm font-extrabold uppercase tracking-[0.3em] text-center">Kompetencer</h2>
                    <div class="mt-5 flex flex-wrap justify-center gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-semibold text-[#a05a64] bg-[#faf0f0] px-4 py-1.5 rounded-full border border-[#eccdd1]">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-10 text-center text-xs text-[#c99aa1]">
                {{ collect([$user->name, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
            </footer>
        </div>

        <div class="rose-gold h-3"></div>
    </div>
</body>
</html>
