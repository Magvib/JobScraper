{{-- Doily Frame --}}
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
        /* Håndarbejdspapir i blød rosélinnen */
        .linen-blush {
            background:
                radial-gradient(ellipse 55% 30% at 100% 100%, rgba(244, 114, 182, .07), transparent 70%),
                radial-gradient(ellipse 45% 28% at 0% 0%, rgba(219, 39, 119, .05), transparent 70%),
                #fbf6f6;
        }
        /* Blondekant: række af halve cirkler skåret ud af kanten */
        .doily-edge {
            height: 12px;
            background:
                radial-gradient(circle 6px at 6px 0px, transparent 5.5px, #fdfbfb 6px);
            background-size: 12px 12px;
            background-repeat: repeat-x;
        }
        .doily-edge-bottom {
            height: 12px;
            background:
                radial-gradient(circle 6px at 6px 12px, transparent 5.5px, #fdfbfb 6px);
            background-size: 12px 12px;
            background-repeat: repeat-x;
        }
        /* Broderet rund medaillon bag initialerne */
        .medallion {
            background:
                repeating-radial-gradient(circle at 50% 50%, transparent 0 6px, rgba(190, 24, 93, .14) 6px 7px, transparent 7px 13px),
                radial-gradient(circle at 50% 50%, #fdf2f8 0 55%, #fce7f3 100%);
            border: 1px solid rgba(190, 24, 93, .25);
            box-shadow: inset 0 0 0 4px #fdfbfb, inset 0 0 0 5px rgba(190, 24, 93, .2);
        }
        .lace-dot {
            width: 5px; height: 5px;
            border-radius: 9999px;
            border: 1px solid rgba(190, 24, 93, .55);
            background: #fdf2f8;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
    $initials = collect(explode(' ', trim($user->name)))->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
@endphp
<body class="bg-white min-h-screen">
    <div class="max-w-[210mm] mx-auto py-6">
        {{-- Doily-kant øverst --}}
        <div class="doily-edge mx-10"></div>

        <div class="cv-page linen-blush mx-10 shadow-lg font-sans text-rose-950/80 px-14 py-11">
            {{-- Medaillon med initialer + foto --}}
            <header class="flex flex-col items-center text-center">
                <div class="relative w-32 h-32">
                    <div class="medallion absolute inset-0 rounded-full grid place-items-center">
                        @if ($photo)
                            <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full object-cover ring-2 ring-rose-200 shadow-sm">
                        @else
                            <span class="text-3xl font-serif font-bold text-rose-800 tracking-widest">{{ $initials }}</span>
                        @endif
                    </div>
                </div>
                <h1 class="mt-6 text-4xl font-bold tracking-wide text-rose-950 leading-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg italic text-rose-700">{{ $user->job_title }}</p>
                @endif
                <div class="mt-5 flex items-center gap-3 text-[12.5px] text-rose-900/70">
                    <span class="lace-dot"></span>
                    <span class="font-semibold">
                        {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
                    </span>
                    <span class="lace-dot"></span>
                </div>
            </header>

            {{-- Blonde-ornament som skillelinje --}}
            <div class="mt-9 flex items-center gap-2 justify-center">
                @foreach (range(1, 13) as $i)
                    <span class="lace-dot" style="opacity: {{ 1 - abs($i - 7) / 9 }};"></span>
                @endforeach
            </div>

            @if (isset($coverLetter))
                <section class="mt-9 max-w-[150mm] mx-auto">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.4em] text-rose-800 text-center">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-rose-950/70 text-justify">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-9 max-w-[155mm] mx-auto">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.4em] text-rose-800 text-center">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="bg-white/70 border border-rose-200/80 rounded-[14px] px-7 py-5 shadow-sm relative">
                                {{-- Hjørne-blondekrus --}}
                                <span class="absolute -top-1.5 left-6 w-3 h-3 rounded-full border border-rose-300 bg-[#fbf6f6]"></span>
                                <span class="absolute -top-1.5 right-6 w-3 h-3 rounded-full border border-rose-300 bg-[#fbf6f6]"></span>
                                <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                    <h3 class="text-lg font-bold text-rose-950 leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-[10px] font-bold tracking-widest text-rose-600 tabular-nums uppercase whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="mt-0.5 text-sm font-semibold text-rose-900/60">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-rose-950/70">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                {{-- Kompetencer i blondekantet skål --}}
                <section class="mt-10 max-w-[155mm] mx-auto">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.4em] text-rose-800 text-center">Kompetencer</h2>
                    <div class="mt-5 flex flex-wrap justify-center gap-2.5">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-rose-800 bg-white/90 border border-rose-200 rounded-full px-4 py-1.5 shadow-sm flex items-center gap-2">
                                <span class="lace-dot"></span>{{ $skill }}
                            </span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-10 max-w-[155mm] mx-auto">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.4em] text-rose-800 text-center">Links</h2>
                    <ul class="mt-5 space-y-2 text-sm text-rose-950/70 text-center">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold text-rose-900">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="underline decoration-rose-400/70 underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-12 flex items-center gap-3 justify-center text-[10px] font-bold uppercase tracking-[0.3em] text-rose-500/70">
                <span class="lace-dot"></span>
                <span>{{ $user->name }} · Håndknyt · {{ now()->format('m.Y') }}</span>
                <span class="lace-dot"></span>
            </footer>
        </div>

        {{-- Doily-kant nederst --}}
        <div class="doily-edge-bottom mx-10"></div>
    </div>
</body>
</html>