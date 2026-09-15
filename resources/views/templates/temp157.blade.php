{{-- Marquee Band --}}
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
        /* Rullende tekst-bånd: statisk, med stjerner mellem gentagelserne */
        .marquee-track {
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-xl min-h-[297mm] font-sans text-neutral-900 overflow-hidden">

        {{-- Løbende tekst-bånd som markise over indholdet --}}
        <div class="bg-yellow-300 text-neutral-900 py-2 border-y-4 border-neutral-900">
            <div class="marquee-track font-black uppercase tracking-[0.2em] text-sm">
                <span class="px-4">{{ $user->name }}</span> ★
                <span class="px-4">{{ $user->job_title ?? 'Curriculum Vitae' }}</span> ★
                <span class="px-4">{{ $user->name }}</span> ★
                <span class="px-4">{{ $user->job_title ?? 'Curriculum Vitae' }}</span> ★
            </div>
        </div>

        <div class="px-14 pt-12 pb-10">
            <header class="flex items-center gap-8 border-b-4 border-neutral-900 pb-8">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-28 h-28 object-cover shrink-0 rounded-full border-4 border-neutral-900 shadow-[6px_6px_0_#000]">
                @endif
                <div>
                    <h1 class="text-5xl font-black tracking-tighter leading-none">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-lg font-bold text-neutral-600">{{ $user->job_title }}</p>
                    @endif
                    <p class="mt-4 text-sm font-semibold text-neutral-500">
                        {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
                    </p>
                </div>
            </header>

            @if (isset($coverLetter))
                <section class="mt-9">
                    <h2 class="inline-block bg-neutral-900 text-yellow-300 text-xs font-extrabold uppercase tracking-[0.3em] px-4 py-2">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-neutral-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-9">
                    <h2 class="inline-block bg-neutral-900 text-yellow-300 text-xs font-extrabold uppercase tracking-[0.3em] px-4 py-2">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="bg-neutral-50 border-2 border-neutral-900 px-6 py-4 shadow-[5px_5px_0_#000]">
                                <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                    <h3 class="font-extrabold text-lg leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-[11px] font-black tracking-widest text-neutral-400 tabular-nums uppercase bg-white border border-neutral-300 px-2.5 py-1 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-neutral-500 mt-0.5">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-neutral-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="inline-block bg-neutral-900 text-yellow-300 text-xs font-extrabold uppercase tracking-[0.3em] px-4 py-2">Kompetencer</h2>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-bold border-2 border-neutral-900 bg-white px-3.5 py-1.5 shadow-[3px_3px_0_#000]">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        {{-- Bånd igen i bunden, spejlet --}}
        <div class="bg-yellow-300 text-neutral-900 py-2 border-t-4 border-neutral-900 mt-auto">
            <div class="marquee-track font-black uppercase tracking-[0.2em] text-sm text-right px-6">
                ★ <span class="px-4">Tak for din tid</span> ★ <span class="px-4">{{ $user->name }}</span> ★ <span class="px-4">{{ now()->format('Y') }}</span>
            </div>
        </div>
    </div>
</body>
</html>