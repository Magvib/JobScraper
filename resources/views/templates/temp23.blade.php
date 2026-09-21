{{-- Museum Plaque --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fbfaf8] text-stone-900 shadow-lg min-h-[297mm] font-serif px-14 py-12 flex flex-col">

        {{-- Indgraveret plakat --}}
        <header class="text-center border-y-4 border-double border-stone-800 py-7">
            <p class="text-[10px] uppercase tracking-[0.5em] text-stone-500">Curriculum Vitae</p>
            <h1 class="mt-3 text-4xl font-bold uppercase tracking-[0.15em] leading-tight">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 text-lg italic text-stone-600">{{ $user->job_title }}</p>
            @endif
            <div class="mt-4 flex flex-wrap justify-center gap-x-5 gap-y-1 text-xs text-stone-600">
                @if ($user->phone)
                    <span>{{ $user->phone }}</span>
                @endif
                <span class="break-all">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>
        </header>

        {{-- Indrammet portræt --}}
        @if ($photo)
            <div class="mt-8 flex justify-center">
                <div class="bg-white border border-stone-800 p-1.5">
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-28 h-28 object-cover">
                </div>
            </div>
        @endif

        <div class="mt-8 grid grid-cols-[1fr_50mm] gap-10 flex-1">
            <main>
                @if (isset($coverLetter))
                    <h2 class="flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.35em] text-stone-700">
                        <span class="flex-1 border-t border-stone-300"></span>
                        Ansøgning
                        <span class="flex-1 border-t border-stone-300"></span>
                    </h2>
                    <div class="mt-6 space-y-4">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                @elseif ($jobs)
                    <h2 class="flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.35em] text-stone-700">
                        <span class="flex-1 border-t border-stone-300"></span>
                        Erhvervserfaring &amp; Uddannelse
                        <span class="flex-1 border-t border-stone-300"></span>
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article>
                                <p class="text-[10px] uppercase tracking-[0.25em] text-stone-500">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm italic text-stone-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </main>

            {{-- Kompetencer & links --}}
            @if ($skills || $links->isNotEmpty())
                <aside>
                    @if ($skills)
                        <h2 class="flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.35em] text-stone-700">
                            <span class="flex-1 border-t border-stone-300"></span>
                            Kompetencer
                            <span class="flex-1 border-t border-stone-300"></span>
                        </h2>
                        <ul class="mt-5 text-sm text-stone-700">
                            @foreach ($skills as $skill)
                                <li class="border-b border-stone-200 py-2">{{ $skill }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($links->isNotEmpty())
                        <h2 class="mt-8 flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.35em] text-stone-700">
                            <span class="flex-1 border-t border-stone-300"></span>
                            Links
                            <span class="flex-1 border-t border-stone-300"></span>
                        </h2>
                        <ul class="mt-5 space-y-3 text-sm">
                            @foreach ($links as $link)
                                <li>
                                    <p class="font-bold text-stone-800">{{ $link->name }}</p>
                                    <a href="{{ $link->url }}" class="text-stone-600 underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </aside>
            @endif
        </div>

        {{-- Afsluttende hairline --}}
        <footer class="mt-10 border-t border-stone-300"></footer>
    </div>
</body>
</html>