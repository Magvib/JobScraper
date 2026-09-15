{{-- Metropolitan Index --}}
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
    $initials = collect(explode(' ', (string) $user->name))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(3)->implode('');
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-xl min-h-[297mm] font-sans text-neutral-900 overflow-hidden">

        {{-- Sort hovedbånd med monogram --}}
        <header class="bg-neutral-900 text-white px-12 py-10 flex items-center gap-8">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover shrink-0 rounded-full border-2 border-orange-500">
            @else
                <div class="w-24 h-24 shrink-0 rounded-full border-2 border-orange-500 flex items-center justify-center text-3xl font-extrabold tracking-wider text-orange-400">
                    {{ $initials }}
                </div>
            @endif
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-orange-400 font-semibold uppercase tracking-[0.2em] text-sm">{{ $user->job_title }}</p>
                @endif
                <p class="mt-3 text-sm text-neutral-300">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </p>
            </div>
        </header>

        <div class="px-12 py-10">
            @php $sectionNo = 1; @endphp

            @if (isset($coverLetter))
                <section>
                    <h2 class="flex items-baseline gap-4">
                        <span class="text-3xl font-extrabold text-orange-500 tabular-nums">{{ str_pad($sectionNo++, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-sm font-bold uppercase tracking-[0.25em]">Ansøgning</span>
                        <span class="flex-1 h-px bg-neutral-200"></span>
                    </h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-neutral-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="flex items-baseline gap-4">
                        <span class="text-3xl font-extrabold text-orange-500 tabular-nums">{{ str_pad($sectionNo++, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-sm font-bold uppercase tracking-[0.25em]">Erhvervserfaring &amp; uddannelse</span>
                        <span class="flex-1 h-px bg-neutral-200"></span>
                    </h2>
                    <div class="mt-7 space-y-7">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[96px_1fr] gap-6">
                                <div class="text-right">
                                    <p class="text-sm font-bold tabular-nums">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}</p>
                                    <p class="text-xs text-neutral-400 font-semibold uppercase tracking-wider mt-0.5">
                                        – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <div class="border-l-4 border-neutral-900 pl-5">
                                    <h3 class="font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-semibold text-orange-600">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-neutral-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="flex items-baseline gap-4">
                        <span class="text-3xl font-extrabold text-orange-500 tabular-nums">{{ str_pad($sectionNo++, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-sm font-bold uppercase tracking-[0.25em]">Kompetencer</span>
                        <span class="flex-1 h-px bg-neutral-200"></span>
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-bold uppercase tracking-wide bg-neutral-900 text-white px-3.5 py-1.5">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-12 bg-neutral-100 -mx-12 -mb-10 px-12 py-4 flex justify-between text-xs font-semibold uppercase tracking-widest text-neutral-500">
                <span>{{ $user->name }}</span>
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>
