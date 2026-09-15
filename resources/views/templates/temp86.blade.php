{{-- Tangerine Tag --}}
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
        .tag { clip-path: polygon(0 0, calc(100% - 12px) 0, 100% 50%, calc(100% - 12px) 100%, 0 100%); }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-orange-950 px-12 py-12">

        <header class="flex items-center gap-7 pb-7 border-b-2 border-orange-200">
            <div class="tag bg-orange-500 text-white pl-6 pr-8 py-4 shrink-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-orange-100">CV</p>
                @if ($user->birthdate)
                    <p class="text-lg font-black tabular-nums">{{ $user->birthdate->format('Y') }}</p>
                @endif
            </div>
            <div class="flex-1">
                <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1 text-lg text-orange-600 font-medium">{{ $user->job_title }}</p>
                @endif
                <p class="mt-2 text-sm text-orange-900/60">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </p>
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-20 h-20 object-cover rounded-xl shrink-0 border-2 border-orange-300">
            @endif
        </header>

        @if (isset($coverLetter))
            <section class="mt-8">
                <div class="tag bg-orange-500 text-white pl-5 pr-7 py-2 inline-block">
                    <h2 class="text-xs font-extrabold uppercase tracking-widest">Ansøgning</h2>
                </div>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <div class="tag bg-orange-500 text-white pl-5 pr-7 py-2 inline-block">
                    <h2 class="text-xs font-extrabold uppercase tracking-widest">Erhvervserfaring &amp; uddannelse</h2>
                </div>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="flex gap-5 items-start">
                            <div class="tag bg-orange-100 text-orange-700 pl-4 pr-6 py-1.5 shrink-0 text-xs font-bold tabular-nums whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}–{{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </div>
                            <div class="flex-1 pt-0.5">
                                <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm font-semibold text-orange-600">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-orange-950/75">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <div class="tag bg-orange-500 text-white pl-5 pr-7 py-2 inline-block">
                    <h2 class="text-xs font-extrabold uppercase tracking-widest">Kompetencer</h2>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="tag text-sm font-semibold text-orange-800 bg-orange-100 pl-4 pr-6 py-1.5">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($user->birthdate)
            <footer class="mt-9 pt-4 border-t border-orange-200 text-xs text-orange-900/50">
                Fødselsdato: {{ $user->birthdate->format('d/m/Y') }}
            </footer>
        @endif
    </div>
</body>
</html>
