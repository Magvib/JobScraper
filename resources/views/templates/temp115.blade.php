{{-- Registry Form --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-neutral-900 px-14 py-12">

        {{-- Formular-style header: feltetiket venstre, værdi højre --}}
        <header class="border-y-2 border-neutral-900 py-7">
            <div class="grid grid-cols-[90px_1fr] items-center gap-x-6">
                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-neutral-400 border-r border-neutral-300 pr-3 self-stretch flex items-center">Navn</p>
                <div class="flex items-center justify-between gap-8">
                    <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-20 h-20 object-cover shrink-0 border border-neutral-900">
                    @endif
                </div>
            </div>
            @if ($user->job_title)
                <div class="grid grid-cols-[90px_1fr] gap-x-6 mt-2.5">
                    <span></span>
                    <p class="text-base font-semibold text-neutral-600">{{ $user->job_title }}</p>
                </div>
            @endif
            <div class="grid grid-cols-[90px_1fr] gap-x-6 mt-5 pt-3 border-t border-neutral-200 text-sm text-neutral-600">
                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-neutral-400 border-r border-neutral-300 pr-3 self-stretch">Kontakt</p>
                <p>
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
                </p>
            </div>
        </header>

        {{-- Sektioner som formularrækker: etiketkolonne + indholdskolonne --}}
        @if (isset($coverLetter))
            <section class="mt-8">
                <div class="grid grid-cols-[90px_1fr] gap-x-6">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.25em] text-neutral-500 border-l-4 border-red-700 pl-2" style="writing-mode: vertical-rl">Ansøgning</h2>
                    <div class="border-b border-neutral-200 pb-8 space-y-4 text-[15px] leading-relaxed text-neutral-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <div class="grid grid-cols-[90px_1fr] gap-x-6">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.25em] text-neutral-500 border-l-4 border-red-700 pl-2" style="writing-mode: vertical-rl">Erfaring</h2>
                    <div class="border-b border-neutral-200 pb-8 space-y-7">
                        @foreach ($jobs as $job)
                            <article>
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold tracking-widest text-red-700 tabular-nums whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-medium text-neutral-500 uppercase tracking-wide">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-neutral-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-8">
                <div class="grid grid-cols-[90px_1fr] gap-x-6">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.25em] text-neutral-500 border-l-4 border-red-700 pl-2" style="writing-mode: vertical-rl">Kompetencer</h2>
                    <div class="border-b border-neutral-200 pb-8 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-neutral-800 border border-neutral-300 px-3.5 py-1.5">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <footer class="mt-9 flex justify-between text-[10px] font-bold uppercase tracking-[0.25em] text-neutral-400">
            <span>Curriculum Vitae</span>
            <span>{{ $user->name }} / {{ now()->format('Y') }}</span>
        </footer>
    </div>
</body>
</html>