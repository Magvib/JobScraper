{{-- Field Almanac --}}
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
        .almanac { font-family: Georgia, 'Times New Roman', serif; }
        /* Dobbeltaviser-kant: kraftig og tynd linje under masthead */
        .masthead-rule { border-bottom: 3px double #7a2e2e; }
        .dropcap::first-letter {
            float: left;
            font-size: 3.1em;
            line-height: 0.85;
            padding: 0.06em 0.12em 0 0;
            font-weight: 700;
            color: #7a2e2e;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto shadow-lg min-h-[297mm] almanac text-[#2f2418] px-12 py-11"
        style="background:
            radial-gradient(ellipse 70% 22% at 50% 0%, rgba(122, 46, 46, .05), transparent 70%),
            #f8f3e7;">

        {{-- Avis-masthead med årgang og udgave --}}
        <header class="masthead-rule pb-4">
            <div class="flex items-baseline justify-between text-[10px] font-bold uppercase tracking-[0.3em] text-[#7a2e2e]">
                <span>Uddrag &amp; Noter</span>
                <span>{{ now()->format('Y') }} · Årgang</span>
            </div>
            <div class="mt-3 flex items-center justify-between gap-6">
                <div>
                    <h1 class="text-5xl font-bold tracking-tight leading-none text-[#2f2418]">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-base italic text-[#7a2e2e]">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <div class="shrink-0 border border-[#7a2e2e]/50 p-1 bg-white shadow-sm rotate-[1.5deg]">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-28 object-cover grayscale contrast-[1.05]">
                    </div>
                @endif
            </div>
            <p class="mt-3 text-[12px] text-[#6b5c49] border-t border-b border-[#7a2e2e]/25 py-1.5 flex justify-between">
                <span>{{ collect([$user->phone, $user->email])->filter()->implode(' · ') }}</span>
                <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            </p>
        </header>

        @if ($skills)
            {{-- Kompetencer som "Vejrudsigt"-linje --}}
            <div class="mt-4 text-[12px]">
                <span class="font-bold uppercase tracking-[0.25em] text-[#7a2e2e] text-[10px]">Kapitler</span>
                <span class="ml-3 text-[#4a3b28]">{{ implode(' · ', $skills) }}</span>
            </div>
        @endif

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.35em] text-[#7a2e2e] text-center border-b border-[#7a2e2e]/30 pb-2">Ansøgning</h2>
                <div class="mt-5 space-y-4 text-[14.5px] leading-relaxed text-[#4a3b28]">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            {{-- Artikler i to spalter, som en avisside --}}
            <section class="mt-8">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.35em] text-[#7a2e2e] text-center border-b border-[#7a2e2e]/30 pb-2">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 columns-2 gap-8 [column-rule:1px_solid_rgba(122,46,46,.25)]">
                    @foreach ($jobs as $job)
                        <article class="break-inside-avoid mb-6">
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#7a2e2e] tabular-nums">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-1 text-lg font-bold leading-snug text-[#2f2418]">{{ $job['title'] }}</h3>
                            <p class="text-[13px] italic text-[#6b5c49]">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-[13px] leading-relaxed text-[#4a3b28] dropcap text-justify">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-6 border-t border-[#7a2e2e]/30 pt-4">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.35em] text-[#7a2e2e]">Korrespondance</h2>
                <ul class="mt-3 text-[13px] text-[#4a3b28]">
                    @foreach ($links as $link)
                        <li class="inline-flex items-baseline">
                            <span class="font-bold">{{ $link->name }}</span><span>&nbsp;—&nbsp;</span>
                            <a href="{{ $link->url }}" class="italic underline decoration-[#7a2e2e]/50 underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>{{ ! $loop->last ? ';' : '.' }}
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-10 pt-3 border-t-[3px] border-double border-[#7a2e2e] flex justify-between text-[10px] font-bold uppercase tracking-[0.3em] text-[#7a2e2e]/80">
            <span>{{ $user->name }}</span>
            <span>Årgangsudklip · {{ now()->format('m.Y') }}</span>
        </footer>
    </div>
</body>
</html>