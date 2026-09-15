{{-- Cherry Blossom --}}
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
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-pink-950 px-14 py-12 relative overflow-hidden">

        {{-- Sakura bobler i hjørnet --}}
        <div class="absolute top-0 right-0 w-40 h-40 bg-pink-100 rounded-full -translate-y-1/2 translate-x-1/3 opacity-70"></div>
        <div class="absolute top-10 right-24 w-12 h-12 bg-pink-200 rounded-full opacity-60"></div>
        <div class="absolute top-24 right-8 w-6 h-6 bg-pink-300 rounded-full opacity-50"></div>

        <header class="relative">
            <div class="flex items-center gap-7">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 object-cover rounded-full shrink-0 ring-4 ring-pink-200">
                @endif
                <div>
                    <h1 class="text-4xl font-light tracking-wide">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-pink-500">{{ $user->job_title }}</p>
                    @endif
                    <p class="mt-2.5 text-sm text-pink-900/60">
                        {{ collect([$user->phone, $user->email, $user->city ? trim(($user->zip ?? '') . ' ' . $user->city) : null])->filter()->implode('  ·  ') }}
                    </p>
                </div>
            </div>
            <div class="mt-7 border-t border-pink-200"></div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-8 relative">
                <h2 class="text-sm font-bold text-pink-500 uppercase tracking-[0.3em]">✿ Ansøgning</h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8 relative">
                <h2 class="text-sm font-bold text-pink-500 uppercase tracking-[0.3em]">✿ Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="flex gap-5 items-start">
                            <span class="shrink-0 w-10 h-10 rounded-full bg-pink-100 text-pink-500 flex items-center justify-center font-black text-sm tabular-nums">{{ $loop->iteration }}</span>
                            <div class="flex-1">
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-lg font-semibold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-semibold text-pink-400 whitespace-nowrap tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm text-pink-900/55 italic">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-pink-950/70">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9 relative">
                <h2 class="text-sm font-bold text-pink-500 uppercase tracking-[0.3em]">✿ Kompetencer</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-sm text-pink-700 bg-pink-50 border border-pink-200 px-3.5 py-1.5 rounded-full">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($user->birthdate)
            <p class="mt-9 text-xs text-pink-900/50 relative">Fødselsdato: {{ $user->birthdate->format('d/m/Y') }}</p>
        @endif
    </div>
</body>
</html>
