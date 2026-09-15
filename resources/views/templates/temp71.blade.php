{{-- Marble Gold --}}
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
        .marble {
            background:
                linear-gradient(115deg, transparent 40%, rgba(180,180,190,0.12) 40.5%, transparent 41%),
                linear-gradient(70deg, transparent 65%, rgba(180,180,190,0.1) 65.5%, transparent 66%),
                linear-gradient(150deg, transparent 25%, rgba(180,180,190,0.08) 25.5%, transparent 26%),
                #fafafa;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page marble max-w-[210mm] mx-auto shadow-lg min-h-[297mm] font-serif text-gray-900 px-16 py-14">

        <header class="text-center pb-6">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover rounded-full mx-auto mb-4 ring-1 ring-yellow-600/60 ring-offset-4 ring-offset-transparent">
            @endif
            <h1 class="text-4xl font-normal tracking-[0.25em] uppercase">{{ $user->name }}</h1>
            <div class="mt-3 flex items-center justify-center gap-3">
                <span class="h-px w-14 bg-yellow-600/70"></span>
                @if ($user->job_title)
                    <p class="text-sm uppercase tracking-[0.3em] text-yellow-700">{{ $user->job_title }}</p>
                @endif
                <span class="h-px w-14 bg-yellow-600/70"></span>
            </div>
            <p class="mt-4 text-sm text-gray-600 font-sans">
                {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? '')), $user->phone, $user->email])->filter()->implode('  ·  ') }}
            </p>
        </header>

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="text-xs uppercase tracking-[0.4em] text-yellow-700 text-center">Ansøgning</h2>
                <div class="mt-5 space-y-4 leading-relaxed text-justify text-[15px]">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="text-xs uppercase tracking-[0.4em] text-yellow-700 text-center">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-7 space-y-6">
                    @foreach ($jobs as $job)
                        <article>
                            <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                <h3 class="text-lg font-semibold">{{ $job['title'] }}</h3>
                                <p class="text-xs tracking-widest text-yellow-700 whitespace-nowrap tabular-nums border-b border-yellow-600/50 pb-0.5">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-sm italic text-gray-600">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-gray-700 font-sans">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-xs uppercase tracking-[0.4em] text-yellow-700 text-center">Kompetencer</h2>
                <p class="mt-4 text-center text-sm font-sans text-gray-700 leading-loose">{{ implode('  ·  ', $skills) }}</p>
            </section>
        @endif

        <footer class="mt-10 flex items-center justify-center gap-3 text-[10px] uppercase tracking-[0.35em] text-gray-500 font-sans">
            <span class="h-px w-10 bg-yellow-600/50"></span>
            {{ collect([$user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null, $user->city])->filter()->implode('  ·  ') }}
            <span class="h-px w-10 bg-yellow-600/50"></span>
        </footer>
    </div>
</body>
</html>
