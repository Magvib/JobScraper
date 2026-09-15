{{-- Charcoal Air --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-neutral-800 px-20 py-16">

        {{-- Minimal luftig header --}}
        <header class="text-center">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-20 h-20 object-cover rounded-full mx-auto grayscale">
            @endif
            <h1 class="mt-5 text-5xl font-thin tracking-[0.15em] uppercase">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-3 text-sm tracking-[0.3em] uppercase text-neutral-500">{{ $user->job_title }}</p>
            @endif
            <p class="mt-6 text-xs tracking-widest text-neutral-500 uppercase">
                {{ collect([$user->phone, $user->email, $user->city ? trim(($user->zip ?? '') . ' ' . $user->city) : null])->filter()->implode('   ·   ') }}
            </p>
        </header>

        @if (isset($coverLetter))
            <section class="mt-14 max-w-xl mx-auto">
                <h2 class="text-center text-[11px] tracking-[0.5em] uppercase text-neutral-400">Ansøgning</h2>
                <div class="mt-8 space-y-5 leading-loose text-[15px]">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-14">
                <h2 class="text-center text-[11px] tracking-[0.5em] uppercase text-neutral-400">Erfaring &amp; uddannelse</h2>
                <div class="mt-10 space-y-10 max-w-xl mx-auto">
                    @foreach ($jobs as $job)
                        <article class="text-center">
                            <p class="text-[11px] tracking-[0.3em] uppercase text-neutral-400 tabular-nums">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                —
                                {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-2 text-xl font-medium tracking-wide">{{ $job['title'] }}</h3>
                            <p class="mt-1 text-sm text-neutral-500 italic font-serif">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-3 text-sm leading-loose text-neutral-600">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-14">
                <h2 class="text-center text-[11px] tracking-[0.5em] uppercase text-neutral-400">Kompetencer</h2>
                <p class="mt-6 text-center text-sm tracking-wider leading-loose text-neutral-600 max-w-lg mx-auto">
                    {{ implode('  ·  ', $skills) }}
                </p>
            </section>
        @endif

        <footer class="mt-16 text-center text-[10px] tracking-[0.4em] uppercase text-neutral-400">
            {{ collect([$user->address, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
        </footer>
    </div>
</body>
</html>
