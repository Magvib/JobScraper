{{-- Offset Print --}}
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
        /* Fejlfarvet tryk: rød og blå afsætning bag navnet */
        .offset-name { position: relative; }
        .offset-name .base { position: relative; z-index: 3; }
        .offset-name::before,
        .offset-name::after {
            content: '{{ $user->name }}';
            position: absolute;
            top: 0;
            left: 0;
            white-space: nowrap;
        }
        .offset-name::before { color: rgba(220, 38, 38, 0.5); transform: translate(5px, -4px); z-index: 1; }
        .offset-name::after { color: rgba(37, 99, 235, 0.5); transform: translate(-5px, 4px); z-index: 2; }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-neutral-900 px-14 py-14 overflow-hidden">

        <header class="pb-8 border-b-4 border-neutral-900 flex items-end justify-between gap-8">
            <div>
                <p class="font-mono text-[10px] uppercase tracking-[0.4em] text-neutral-400">curriculum vitae · trykt {{ now()->format('d.m.Y') }}</p>
                <h1 class="offset-name mt-4 text-5xl font-black tracking-tight">
                    <span class="base">{{ $user->name }}</span>
                </h1>
                @if ($user->job_title)
                    <p class="mt-3 text-base font-semibold text-neutral-500">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <div class="shrink-0 relative">
                    <span class="absolute inset-0 bg-blue-500/40 translate-x-1.5 -translate-y-1.5"></span>
                    <span class="absolute inset-0 bg-red-500/40 -translate-x-1.5 translate-y-1.5"></span>
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="relative w-24 h-24 object-cover grayscale border border-neutral-900">
                </div>
            @endif
        </header>
        <p class="mt-5 font-mono text-xs text-neutral-500">
            {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
        </p>

        @if (isset($coverLetter))
            <section class="mt-9">
                <h2 class="font-mono text-xs font-bold uppercase tracking-[0.3em] text-neutral-900 border-l-4 border-red-500 pl-3">Ansøgning</h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-neutral-700">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-9">
                <h2 class="font-mono text-xs font-bold uppercase tracking-[0.3em] text-neutral-900 border-l-4 border-red-500 pl-3">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="grid grid-cols-[110px_1fr] gap-6">
                            <p class="text-right font-mono text-xs font-bold text-blue-600 tabular-nums pt-1.5 uppercase">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>
                                <span class="text-red-500">– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}</span>
                            </p>
                            <div class="border-b-2 border-neutral-900 pb-5">
                                <h3 class="font-extrabold text-lg leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm font-semibold text-neutral-500">{{ $job['company'] }}</p>
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
                <h2 class="font-mono text-xs font-bold uppercase tracking-[0.3em] text-neutral-900 border-l-4 border-blue-500 pl-3">Kompetencer</h2>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="font-mono text-[13px] font-semibold text-neutral-800 border border-neutral-900 bg-white shadow-[2px_2px_0_0_#dc2626,4px_4px_0_0_#2563eb] px-3.5 py-1.5">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-14 pt-4 border-t-4 border-neutral-900 flex justify-between font-mono text-[10px] uppercase tracking-[0.3em] text-neutral-400">
            <span>{{ $user->name }}</span>
            <span>off-set print</span>
        </footer>
    </div>
</body>
</html>