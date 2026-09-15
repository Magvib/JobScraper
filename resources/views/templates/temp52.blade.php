{{-- Greyscale Brief --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-gray-800 grid grid-cols-[64mm_1fr]">

        {{-- Lys grå sidebar, ingen farver --}}
        <aside class="bg-gray-100 px-7 py-10 border-r border-gray-200">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-28 object-cover grayscale mb-8 mx-auto">
            @endif

            <section>
                <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-500">Kontakt</h2>
                <ul class="mt-2.5 space-y-1.5 text-sm text-gray-700">
                    @if ($user->phone)
                        <li>{{ $user->phone }}</li>
                    @endif
                    <li class="break-all">{{ $user->email }}</li>
                    @if ($user->address || $user->city)
                        <li>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</li>
                    @endif
                    @if ($user->birthdate)
                        <li>Født {{ $user->birthdate->format('d/m/Y') }}</li>
                    @endif
                </ul>
            </section>

            @if ($skills)
                <section class="mt-8">
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-500">Kompetencer</h2>
                    <ul class="mt-2.5 space-y-1 text-sm text-gray-700">
                        @foreach ($skills as $skill)
                            <li>— {{ $skill }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>

        <main class="px-10 py-10">
            <header>
                <h1 class="text-4xl font-light tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-base text-gray-500">{{ $user->job_title }}</p>
                @endif
                <div class="mt-5 h-px bg-gray-300"></div>
            </header>

            @if (isset($coverLetter))
                <section class="mt-7">
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-500">Ansøgning</h2>
                    <div class="mt-4 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-7">
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-500">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-5 space-y-6">
                        @foreach ($jobs as $job)
                            <article>
                                <h3 class="font-semibold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    {{ $job['company'] }}
                                    ·
                                    <span class="tabular-nums">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}</span>
                                </p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-gray-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </main>
    </div>
</body>
</html>
