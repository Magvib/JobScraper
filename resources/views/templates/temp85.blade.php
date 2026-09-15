{{-- Blush Cards --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-rose-50/70 shadow-md min-h-[297mm] font-sans text-rose-950 px-10 py-10">

        {{-- Kort-baseret layout --}}
        <header class="bg-white rounded-2xl shadow-sm px-9 py-8 flex items-center gap-7">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-20 h-20 object-cover rounded-2xl shrink-0">
            @endif
            <div class="flex-1">
                <h1 class="text-3xl font-bold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1 text-rose-500 font-medium">{{ $user->job_title }}</p>
                @endif
            </div>
            <div class="text-right text-xs text-rose-900/60 space-y-1">
                @if ($user->phone)
                    <p>{{ $user->phone }}</p>
                @endif
                <p class="break-all">{{ $user->email }}</p>
                @if ($user->address || $user->city)
                    <p>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                @endif
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-5 bg-white rounded-2xl shadow-sm px-9 py-7">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-rose-400">Ansøgning</h2>
                <div class="mt-4 space-y-4 text-[15px] leading-relaxed">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-5 space-y-4">
                @foreach ($jobs as $job)
                    <article class="bg-white rounded-2xl shadow-sm px-8 py-5 flex gap-6 items-start">
                        <p class="shrink-0 text-[11px] font-bold text-rose-500 bg-rose-100 rounded-lg px-2.5 py-1.5 tabular-nums whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>
                            {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                        </p>
                        <div class="flex-1">
                            <h3 class="font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                            <p class="text-sm text-rose-400 font-medium">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-1.5 leading-relaxed text-rose-950/70">{{ $job['description'] }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>
        @endif

        <div class="mt-5 grid {{ $user->birthdate ? 'grid-cols-[1fr_auto]' : 'grid-cols-1' }} gap-4">
            @if ($skills)
                <section class="bg-white rounded-2xl shadow-sm px-8 py-5">
                    <h2 class="text-xs font-extrabold uppercase tracking-widest text-rose-400">Kompetencer</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-xs font-semibold text-rose-700 bg-rose-100 px-3 py-1 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
            @if ($user->birthdate)
                <section class="bg-white rounded-2xl shadow-sm px-8 py-5">
                    <h2 class="text-xs font-extrabold uppercase tracking-widest text-rose-400">Født</h2>
                    <p class="mt-3 text-sm font-semibold">{{ $user->birthdate->format('d/m/Y') }}</p>
                </section>
            @endif
        </div>
    </div>
</body>
</html>
