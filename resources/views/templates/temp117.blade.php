{{-- Ledger Ruled --}}
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
        /* Kuglepen-ruled linjer som notesblok */
        .ruled {
            background-image: repeating-linear-gradient(
                to bottom,
                transparent 0,
                transparent 31px,
                #c9d4e3 31px,
                #c9d4e3 32px
            );
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page relative max-w-[210mm] mx-auto bg-white ruled shadow-md min-h-[297mm] font-sans text-slate-800 overflow-hidden">

        {{-- Rød margenlinje som i en kladdebog --}}
        <div class="absolute left-19 top-0 bottom-0 w-px bg-red-400/60"></div>

        <div class="px-25 pt-12 pb-14">
            <header class="mb-10">
                <div class="flex items-start justify-between gap-8">
                    <div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                        @if ($user->job_title)
                            <p class="mt-1.5 text-base font-semibold text-blue-700">{{ $user->job_title }}</p>
                        @endif
                    </div>
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-20 h-20 object-cover shrink-0 border border-slate-300 shadow-sm">
                    @endif
                </div>
                <p class="mt-4 text-sm text-slate-500">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </p>
            </header>

            @if (isset($coverLetter))
                <section>
                    <h2 class="inline-block text-xs font-extrabold uppercase tracking-widest text-white bg-red-500 px-3 py-1.5 -ml-3 shadow-sm">Ansøgning</h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-8 text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="inline-block text-xs font-extrabold uppercase tracking-widest text-white bg-red-500 px-3 py-1.5 -ml-3 shadow-sm">Erhvervserfaring &amp; Uddannelse</h2>
                    <div class="mt-7 space-y-7">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[105px_1fr] gap-6">
                                <div class="text-right">
                                    <p class="text-xs font-bold text-slate-500 tabular-nums leading-5">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}</p>
                                    <p class="text-xs text-slate-400 tabular-nums leading-5">– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}</p>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg leading-8 text-slate-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-blue-700 leading-8">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm leading-8 text-slate-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="inline-block text-xs font-extrabold uppercase tracking-widest text-white bg-red-500 px-3 py-1.5 -ml-3 shadow-sm">Kompetencer</h2>
                    <p class="mt-5 text-sm leading-8 font-medium text-slate-700">
                        {{ implode('   ·   ', $skills) }}
                    </p>
                </section>
            @endif

            <footer class="mt-12 text-xs text-slate-400 leading-8">
                @if ($user->birthdate)
                    Født {{ $user->birthdate->format('d/m/Y') }}  ·
                @endif
                {{ $user->name }}
            </footer>
        </div>
    </div>
</body>
</html>