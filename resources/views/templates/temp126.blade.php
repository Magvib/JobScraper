{{-- Stacked Sheets --}}
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
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto min-h-[297mm] py-8 px-6">

        {{-- Bagvedliggende skæve ark --}}
        <div class="relative">
            <div class="absolute inset-0 bg-stone-200 rotate-[1.2deg] rounded-sm shadow-md"></div>
            <div class="absolute inset-0 bg-stone-100 -rotate-[0.6deg] rounded-sm shadow-md"></div>

            {{-- Øverste ark --}}
            <div class="relative bg-white shadow-lg px-14 py-12 min-h-[281mm] font-sans text-stone-800">
                <header class="pb-7 border-b-2 border-stone-900 flex items-start justify-between gap-8">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-stone-400">Curriculum Vitae</p>
                        <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-stone-900">{{ $user->name }}</h1>
                        @if ($user->job_title)
                            <p class="mt-1.5 text-base font-semibold text-orange-600">{{ $user->job_title }}</p>
                        @endif
                        <p class="mt-4 text-sm text-stone-500">
                            {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                        </p>
                    </div>
                    @if ($photo)
                        <div class="shrink-0 border border-stone-300 p-1.5 bg-white shadow-sm rotate-2">
                            <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover">
                        </div>
                    @endif
                </header>

                @if (isset($coverLetter))
                    <section class="mt-8">
                        <h2 class="text-sm font-extrabold uppercase tracking-[0.25em] text-stone-900 flex items-center gap-3">
                            <span class="h-2.5 w-2.5 bg-orange-500 rotate-3 rounded-sm"></span> Ansøgning
                        </h2>
                        <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-700">
                            {!! $coverLetter->renderContext() !!}
                        </div>
                    </section>
                @elseif ($jobs)
                    <section class="mt-8">
                        <h2 class="text-sm font-extrabold uppercase tracking-[0.25em] text-stone-900 flex items-center gap-3">
                            <span class="h-2.5 w-2.5 bg-orange-500 rotate-3 rounded-sm"></span> Erhvervserfaring &amp; uddannelse
                        </h2>
                        <div class="mt-6 space-y-6">
                            @foreach ($jobs as $job)
                                <article class="grid grid-cols-[105px_1fr] gap-5">
                                    <p class="text-right text-xs font-bold text-stone-400 tabular-nums pt-1 uppercase tracking-wider">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <div class="border-l-2 border-orange-400 pl-5">
                                        <h3 class="font-bold text-lg leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                        <p class="text-sm font-medium text-stone-500">{{ $job['company'] }}</p>
                                        @if (!empty($job['description']))
                                            <p class="text-sm mt-2 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($skills)
                    <section class="mt-9">
                        <h2 class="text-sm font-extrabold uppercase tracking-[0.25em] text-stone-900 flex items-center gap-3">
                            <span class="h-2.5 w-2.5 bg-orange-500 rotate-3 rounded-sm"></span> Kompetencer
                        </h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($skills as $skill)
                                <span class="text-[13px] font-semibold text-stone-700 bg-stone-100 border border-stone-300 px-3 py-1.5 rounded">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif

                <footer class="mt-11 pt-4 border-t border-dashed border-stone-300 flex justify-between text-xs text-stone-400 tracking-wide">
                    <span>{{ $user->name }}</span>
                    @if ($user->birthdate)
                        <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                    @endif
                </footer>
            </div>
        </div>
    </div>
</body>
</html>