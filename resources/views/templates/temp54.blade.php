{{-- Indigo Sidebar Right --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-gray-900 grid grid-cols-[1fr_66mm]">

        {{-- Hovedkolonne venstre --}}
        <main class="px-10 py-12">
            <header>
                <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg text-indigo-800 font-medium">{{ $user->job_title }}</p>
                @endif
                <div class="mt-5 h-1 w-20 bg-indigo-700"></div>
            </header>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-indigo-900">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-indigo-900">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article>
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold text-indigo-700 whitespace-nowrap tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm italic text-gray-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-gray-700">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </main>

        {{-- Indigo sidebar højre --}}
        <aside class="bg-indigo-900 text-indigo-100 px-7 py-12 flex flex-col gap-9">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 object-cover rounded-xl border-2 border-indigo-400 mx-auto">
            @endif

            <section>
                <h2 class="text-[11px] font-bold uppercase tracking-[0.25em] text-indigo-300 border-b border-indigo-700 pb-2">Kontakt</h2>
                <ul class="mt-3 space-y-2 text-sm">
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
                <section>
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.25em] text-indigo-300 border-b border-indigo-700 pb-2">Kompetencer</h2>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($skills as $skill)
                            <span class="text-xs font-medium bg-indigo-800 text-indigo-100 px-2.5 py-1 rounded">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>
    </div>
</body>
</html>
