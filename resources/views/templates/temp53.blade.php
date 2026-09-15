{{-- Paper Simple --}}
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
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md px-18 py-16 font-serif text-gray-900 min-h-[297mm]" style="padding-left:4.5rem;padding-right:4.5rem;">

        {{-- Ren serif, centeret, ingen farver --}}
        <header class="text-center">
            <h1 class="text-4xl font-normal tracking-wide">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 italic text-gray-600">{{ $user->job_title }}</p>
            @endif
            <p class="mt-4 text-sm text-gray-600">
                {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? '')), $user->phone, $user->email])->filter()->implode('  |  ') }}
            </p>
            <div class="mt-6 mx-auto w-24 border-t border-gray-400"></div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-10">
                <h2 class="text-sm font-bold tracking-widest uppercase text-center">Ansøgning</h2>
                <div class="mt-6 space-y-4 leading-relaxed text-justify">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-10">
                <h2 class="text-sm font-bold tracking-widest uppercase text-center">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-7 space-y-7">
                    @foreach ($jobs as $job)
                        <article>
                            <div class="flex items-baseline gap-3">
                                <span class="text-xs text-gray-500 tabular-nums shrink-0 w-24">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </span>
                                <div>
                                    <h3 class="font-bold leading-snug">{{ $job['title'] }} <span class="font-normal italic text-gray-600">— {{ $job['company'] }}</span></h3>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-1.5 leading-relaxed text-gray-700">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-10">
                <h2 class="text-sm font-bold tracking-widest uppercase text-center">Kompetencer</h2>
                <p class="mt-4 text-sm text-center text-gray-700">{{ implode('  ·  ', $skills) }}</p>
            </section>
        @endif

        <footer class="mt-12 pt-4 border-t border-gray-300 text-center text-xs text-gray-500">
            {{ collect([$user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null, $user->phone, $user->email])->filter()->implode('  |  ') }}
        </footer>
    </div>
</body>
</html>
