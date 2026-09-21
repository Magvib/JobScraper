{{-- Nordic Frost --}}
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
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-sky-950 grid grid-cols-[60mm_1fr]">

        {{-- Frostet sidebar --}}
        <aside class="bg-gradient-to-b from-sky-50 to-cyan-100/60 px-7 py-12 border-r border-sky-100 flex flex-col gap-8">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-36 object-cover mx-auto shadow-md border-4 border-white">
            @endif

            <section>
                <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-sky-500">Kontakt</h2>
                <ul class="mt-2.5 space-y-2 text-sm text-sky-900/80">
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
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-sky-500">Kompetencer</h2>
                    <div class="mt-3 space-y-2">
                        @foreach ($skills as $skill)
                            <div>
                                <p class="text-xs font-semibold text-sky-900">{{ $skill }}</p>
                                <div class="mt-1 h-1.5 rounded-full bg-white border border-sky-200">
                                    <div class="h-full w-3/4 rounded-full bg-gradient-to-r from-sky-300 to-cyan-400"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-sky-500">Links</h2>
                    <ul class="mt-2.5 space-y-2 text-sm text-sky-900/80">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold text-sky-900">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>

        <main class="px-10 py-12">
            <header>
                <p class="text-[10px] font-bold tracking-[0.4em] uppercase text-cyan-500">CV</p>
                <h1 class="mt-2 text-4xl font-light tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg text-sky-600">{{ $user->job_title }}</p>
                @endif
                <div class="mt-5 h-0.5 w-24 bg-gradient-to-r from-sky-400 to-cyan-300 rounded-full"></div>
            </header>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="text-xs font-bold uppercase tracking-[0.25em] text-sky-400">Ansøgning</h2>
                    <div class="mt-4 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="text-xs font-bold uppercase tracking-[0.25em] text-sky-400">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-5 space-y-6">
                        @foreach ($jobs as $job)
                            <article>
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-lg font-semibold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-semibold text-cyan-600 whitespace-nowrap tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm text-sky-700 italic">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-1.5 leading-relaxed text-sky-950/70">{{ $job['description'] }}</p>
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
