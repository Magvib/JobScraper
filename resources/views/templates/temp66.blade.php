{{-- Lavender Soft --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-violet-50/50 shadow-md min-h-[297mm] font-sans text-violet-950 px-12 py-12">

        {{-- Blød lavendel header --}}
        <header class="text-center">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 object-cover rounded-full mx-auto ring-8 ring-violet-100 shadow-md">
            @endif
            <h1 class="mt-5 text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 inline-block bg-violet-200/70 text-violet-900 text-sm font-semibold px-4 py-1.5 rounded-full">{{ $user->job_title }}</p>
            @endif
            <p class="mt-4 text-sm text-violet-900/60">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </p>
        </header>

        @if (isset($coverLetter))
            <section class="mt-10">
                <h2 class="text-center">
                    <span class="bg-violet-600 text-white text-xs font-bold uppercase tracking-[0.25em] px-6 py-2 rounded-full">Ansøgning</span>
                </h2>
                <div class="mt-6 bg-white rounded-3xl shadow-sm p-8 space-y-4 text-[15px] leading-relaxed">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-10">
                <h2 class="text-center">
                    <span class="bg-violet-600 text-white text-xs font-bold uppercase tracking-[0.25em] px-6 py-2 rounded-full">Erfaring &amp; uddannelse</span>
                </h2>
                <div class="mt-6 space-y-4">
                    @foreach ($jobs as $job)
                        <article class="bg-white rounded-3xl shadow-sm px-7 py-5">
                            <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-xs font-bold text-violet-600 whitespace-nowrap tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-sm font-medium text-violet-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-violet-950/70">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-center">
                    <span class="bg-violet-600 text-white text-xs font-bold uppercase tracking-[0.25em] px-6 py-2 rounded-full">Kompetencer</span>
                </h2>
                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-sm font-semibold text-violet-800 bg-white border-2 border-violet-200 px-3.5 py-1.5 rounded-full">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($user->birthdate)
            <p class="mt-10 text-center text-xs text-violet-900/50">Fødselsdato: {{ $user->birthdate->format('d/m/Y') }}</p>
        @endif
    </div>
</body>
</html>
