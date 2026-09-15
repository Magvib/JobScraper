{{-- Bordeaux Diploma --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-serif text-gray-900 border-[10px] border-double border-red-900 p-12">

        {{-- Diplom header --}}
        <header class="text-center pb-6 border-b border-red-900">
            <p class="text-[10px] uppercase tracking-[0.5em] text-red-800">Curriculum Vitae</p>
            <h1 class="mt-3 text-4xl font-bold small-caps tracking-wider" style="font-variant: small-caps;">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 text-lg italic text-gray-700">{{ $user->job_title }}</p>
            @endif
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-20 h-24 object-cover mx-auto mt-4 border border-red-900 p-1">
            @endif
            <p class="mt-4 text-sm text-gray-600">
                {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? '')), $user->phone, $user->email, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode(' — ') }}
            </p>
        </header>

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="text-center text-sm font-bold tracking-[0.3em] uppercase text-red-900">
                    <span class="border-y border-red-900 py-1 px-6">Ansøgning</span>
                </h2>
                <div class="mt-6 space-y-4 leading-relaxed text-justify">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="text-center text-sm font-bold tracking-[0.3em] uppercase text-red-900">
                    <span class="border-y border-red-900 py-1 px-6">Erhvervserfaring &amp; uddannelse</span>
                </h2>
                <div class="mt-7 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="grid grid-cols-[150px_1fr] gap-5 items-start">
                            <p class="text-xs font-semibold text-red-900 text-right pt-1 leading-relaxed">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>–<br>
                                {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <div class="border-l border-gray-300 pl-5">
                                <h3 class="font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm italic text-gray-600">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-justify">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-center text-sm font-bold tracking-[0.3em] uppercase text-red-900">
                    <span class="border-y border-red-900 py-1 px-6">Kompetencer</span>
                </h2>
                <ul class="mt-5 flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm">
                    @foreach ($skills as $skill)
                        <li class="flex items-center gap-2">
                            <span class="text-red-800">◆</span>{{ $skill }}
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-10 flex justify-center">
            <span class="border-y border-red-900 py-1 px-8 text-[10px] uppercase tracking-[0.4em] text-gray-600">{{ $user->name }}</span>
        </footer>
    </div>
</body>
</html>
