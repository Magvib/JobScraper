{{-- Copper Foil --}}
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
        /* Mørk lakeret papir med varm glød */
        .lacquer {
            background:
                radial-gradient(ellipse 55% 30% at 85% 0%, rgba(185, 124, 65, .16), transparent 70%),
                linear-gradient(180deg, #211a15, #2a211a 55%, #211a15);
        }
        /* Kobberfolie-tekst til navnet */
        .foil {
            background: linear-gradient(100deg, #f5d3a1 0%, #c98d4e 35%, #f7e3c2 55%, #b97c41 75%, #f5d3a1 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .foil-rule { background: linear-gradient(90deg, transparent, #d9a869 20%, #f7e3c2 50%, #d9a869 80%, transparent); }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page lacquer max-w-[210mm] mx-auto shadow-xl min-h-[297mm] font-sans text-stone-300 overflow-hidden px-14 py-12 relative">

        {{-- Tynd kobberfolie-ramme rundt om siden --}}
        <div class="pointer-events-none absolute inset-3 border border-amber-700/40"></div>
        <div class="pointer-events-none absolute inset-[15px] border border-amber-500/15"></div>

        <div class="relative pt-4">
            <header class="flex items-end justify-between gap-8">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.5em] text-amber-600/90">Curriculum Vitae</p>
                    <h1 class="mt-3 text-5xl font-bold tracking-tight leading-none foil">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-3 text-lg font-medium text-amber-200/90 tracking-wide">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <div class="shrink-0 w-28 h-28 rounded-full p-[3px] bg-gradient-to-br from-amber-300 via-amber-600 to-amber-900 shadow-lg">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-full">
                    </div>
                @endif
            </header>

            <div class="mt-7 h-px foil-rule"></div>

            <p class="mt-5 text-[13px] text-stone-400 tracking-wide">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('   ·   ') }}
            </p>

            @if (isset($coverLetter))
                <section class="mt-10">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-amber-300">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-300/95">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-10">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-amber-300">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[130px_1fr] gap-6">
                                <p class="pt-1 text-[11px] font-bold tracking-widest text-amber-500/90 tabular-nums uppercase leading-relaxed">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>— {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <div>
                                    <h3 class="text-xl font-semibold text-amber-50 leading-snug">{{ $job['title'] }}</h3>
                                    <p class="mt-0.5 text-sm font-medium text-amber-200/70">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-stone-400">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-amber-300">Kompetencer</h2>
                    <ul class="mt-5 grid grid-cols-2 gap-x-8 gap-y-2.5">
                        @foreach ($skills as $skill)
                            <li class="flex items-center gap-3 text-[13px] text-stone-300">
                                <span class="w-1 h-4 rotate-[20deg] bg-gradient-to-b from-amber-300 to-amber-700 rounded-full shrink-0"></span>{{ $skill }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-10">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-amber-300">Links</h2>
                    <ul class="mt-5 space-y-1.5 text-sm text-stone-400">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold text-amber-100">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-amber-300/90 underline decoration-amber-700/60 underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <div class="mt-12 h-px foil-rule"></div>
            <footer class="mt-4 flex justify-between text-[10px] font-semibold uppercase tracking-[0.35em] text-stone-500">
                <span>{{ $user->name }}</span>
                <span>Forseglet · {{ now()->format('m.Y') }}</span>
            </footer>
        </div>
    </div>
</body>
</html>