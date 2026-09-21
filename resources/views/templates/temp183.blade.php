{{-- Loom Weft --}}
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
        /* Rå lærred: strame med fine kæde-og-indslag-tråde */
        .canvas-cloth {
            background:
                repeating-linear-gradient(0deg, transparent 0 3px, rgba(120, 113, 108, .045) 3px 4px),
                repeating-linear-gradient(90deg, transparent 0 3px, rgba(120, 113, 108, .045) 3px 4px),
                #faf7f0;
        }
        /* Vævet bånd: to farver flettes som kæde og indslag */
        .woven-band {
            height: 26px;
            background:
                repeating-linear-gradient(90deg, #0f766e 0 14px, #134e4a 14px 28px),
                repeating-linear-gradient(0deg, transparent 0 5px, rgba(255, 255, 255, .22) 5px 7px, transparent 7px 12px, rgba(255, 255, 255, .25) 12px 13px);
            background-blend-mode: screen;
            border-top: 1px solid rgba(19, 78, 74, .8);
            border-bottom: 1px solid rgba(19, 78, 74, .5);
        }
        /* Kvastehale: frynser under båndet */
        .fringe {
            height: 12px;
            background:
                repeating-linear-gradient(90deg, #0f766e 0 2px, transparent 2px 6px, #134e4a 6px 7px, transparent 7px 11px);
            background-size: 100% 100%;
            opacity: .8;
        }
        /* Trådpunkt i stedet for kugle */
        .thread-dot {
            width: 22px; height: 6px;
            border-radius: 9999px;
            background: linear-gradient(90deg, #0f766e, #5eead4 50%, #0f766e);
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
    <div class="cv-page canvas-cloth max-w-[210mm] mx-auto shadow-lg min-h-[297mm] font-sans text-stone-700 px-14 py-12">

        {{-- Vævet bånd hen over toppen med frynser --}}
        <div class="-mx-14 -mt-12 mb-10">
            <div class="woven-band"></div>
            <div class="fringe mx-8"></div>
        </div>

        <header class="flex items-center gap-9">
            @if ($photo)
                <div class="shrink-0 relative">
                    {{-- Foto i firkantet "væv" med trådramme --}}
                    <div class="p-1.5 bg-white border border-stone-300 shadow-md">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-28 h-32 object-cover">
                        <div class="mt-1 h-[3px] bg-gradient-to-r from-teal-700 via-teal-400 to-teal-700 rounded-full"></div>
                    </div>
                </div>
            @endif
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-teal-800/90">Vævet efter mønster</p>
                <h1 class="mt-2.5 text-4xl font-extrabold tracking-tight text-stone-800 leading-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-lg font-semibold text-teal-700">{{ $user->job_title }}</p>
                @endif
                <p class="mt-3.5 text-[13px] text-stone-500">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('   ·   ') }}
                </p>
            </div>
        </header>

        {{-- Vandret tråd under header --}}
        <div class="mt-7 flex items-center gap-3">
            <span class="thread-dot"></span>
            <span class="flex-1 h-px bg-stone-300"></span>
        </div>

        @if (isset($coverLetter))
            <section class="mt-9">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.4em] text-teal-800">Ansøgning</h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-600 max-w-[158mm]">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            {{-- Hvert erhverv en vævet stribe --}}
            <section class="mt-9">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.4em] text-teal-800">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="relative pl-9">
                            {{-- Indslagstråd --}}
                            <span class="thread-dot absolute left-0 top-2.5"></span>
                            <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                <h3 class="text-lg font-bold text-stone-800 leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-[10px] font-bold tracking-widest text-teal-700 tabular-nums uppercase whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="mt-0.5 text-sm font-semibold text-stone-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-stone-600 max-w-[150mm]">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            {{-- Kompetencer som garnnøgler --}}
            <section class="mt-10">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.4em] text-teal-800">Kompetencer</h2>
                <div class="mt-5 grid grid-cols-2 gap-x-10 gap-y-2.5">
                    @foreach ($skills as $skill)
                        <div class="flex items-center gap-2.5 text-[13px] font-medium text-stone-700">
                            <span class="w-5 h-5 rounded-full border-2 border-teal-700/50 overflow-hidden shrink-0"
                                style="background: repeating-radial-gradient(circle at 50% 50%, #0f766e 0 2px, #99f6e4 2px 4px);"></span>
                            {{ $skill }}
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.4em] text-teal-800">Links</h2>
                <ul class="mt-5 space-y-1.5 text-sm text-stone-600">
                    @foreach ($links as $link)
                        <li>
                            <span class="font-semibold text-stone-800">{{ $link->name }}:</span>
                            <a href="{{ $link->url }}" class="text-teal-800 underline decoration-teal-500/60 underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Nederste vævede kant med frynser --}}
        <div class="mt-12 -mx-14 -mb-12">
            <div class="fringe mx-8" style="transform: scaleY(-1);"></div>
            <div class="woven-band" style="transform: scaleY(-1);"></div>
        </div>
    </div>
</body>
</html>