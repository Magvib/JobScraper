{{-- Mint Fresh --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-emerald-950">

        {{-- Frisk mint topkant --}}
        <div class="h-2 bg-gradient-to-r from-emerald-300 via-teal-300 to-emerald-300"></div>

        <div class="px-12 py-10">
            <header class="flex items-center gap-6">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-20 h-20 object-cover rounded-full shrink-0 border-4 border-emerald-100">
                @endif
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1 text-emerald-600 font-medium">{{ $user->job_title }}</p>
                    @endif
                    <p class="mt-2 text-sm text-emerald-900/60">
                        {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                    </p>
                </div>
            </header>

            @if (isset($coverLetter))
                <section class="mt-9 bg-emerald-50/50 rounded-2xl p-7">
                    <h2 class="text-xs font-extrabold uppercase tracking-widest text-emerald-600">Ansøgning</h2>
                    <div class="mt-4 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-9">
                    <h2 class="text-xs font-extrabold uppercase tracking-widest text-emerald-600">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-5 space-y-1">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5">
                                <div class="flex flex-col items-center shrink-0">
                                    <span class="w-3 h-3 rounded-full bg-emerald-400 mt-1.5"></span>
                                    @if (!$loop->last)
                                        <span class="w-px flex-1 bg-emerald-200"></span>
                                    @endif
                                </div>
                                <div class="pb-6">
                                    <p class="text-xs font-bold text-emerald-600 tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <h3 class="mt-1 font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm text-emerald-900/60 font-medium">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-emerald-950/75">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-8">
                    <h2 class="text-xs font-extrabold uppercase tracking-widest text-emerald-600">Kompetencer</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-medium text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-lg">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($user->birthdate)
                <p class="mt-8 text-xs text-emerald-900/50">Fødselsdato: {{ $user->birthdate->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>
</body>
</html>
