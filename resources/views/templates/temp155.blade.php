{{-- Print Sheet --}}
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
        /* Beskæringsmærker i alle fire hjørner */
        .crop { position: absolute; width: 24px; height: 24px; pointer-events: none; }
        .crop.tl { top: 10px; left: 10px; border-top: 1px solid #111; border-left: 1px solid #111; }
        .crop.tr { top: 10px; right: 10px; border-top: 1px solid #111; border-right: 1px solid #111; }
        .crop.bl { bottom: 10px; left: 10px; border-bottom: 1px solid #111; border-left: 1px solid #111; }
        .crop.br { bottom: 10px; right: 10px; border-bottom: 1px solid #111; border-right: 1px solid #111; }
        /* Farveprøvebjælke som i trykkeri */
        .colorbar { display: flex; }
        .colorbar span { width: 18px; height: 10px; display: block; }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page relative max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-neutral-900 px-14 py-14 overflow-hidden">

        <span class="crop tl"></span>
        <span class="crop tr"></span>
        <span class="crop bl"></span>
        <span class="crop br"></span>

        {{-- Trykarkets tekniske header --}}
        <div class="absolute top-11 right-14 text-right font-mono text-[9px] uppercase tracking-[0.2em] text-neutral-400 leading-relaxed">
            <p>Format A4 · 210×297</p>
            <p>Tryk {{ now()->format('d.m.Y') }}</p>
        </div>
        <div class="absolute bottom-11 left-14">
            <div class="colorbar">
                <span style="background:#000"></span><span style="background:#dc2626"></span><span style="background:#f59e0b"></span><span style="background:#22c55e"></span><span style="background:#0ea5e9"></span><span style="background:#a855f7"></span>
            </div>
        </div>

        <header class="pb-8 border-b-2 border-neutral-900 pr-40">
            <p class="font-mono text-[10px] uppercase tracking-[0.4em] text-neutral-400">Galleriark · Curriculum Vitae</p>
            <h1 class="mt-4 text-5xl font-extrabold tracking-tight leading-none">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-3 text-lg font-semibold text-neutral-500">{{ $user->job_title }}</p>
            @endif
        </header>
        <p class="mt-5 font-mono text-xs text-neutral-500">
            {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
        </p>

        <div class="mt-9 grid grid-cols-[1fr_170px] gap-10">
            <div>
                @if (isset($coverLetter))
                    <section>
                        <h2 class="font-mono text-xs font-bold uppercase tracking-[0.3em] text-neutral-900 border-t border-neutral-900 pt-2">Ansøgning</h2>
                        <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-neutral-700">
                            {!! $coverLetter->renderContext() !!}
                        </div>
                    </section>
                @elseif ($jobs)
                    <section>
                        <h2 class="font-mono text-xs font-bold uppercase tracking-[0.3em] text-neutral-900 border-t border-neutral-900 pt-2">Erhvervserfaring &amp; uddannelse</h2>
                        <div class="mt-5 space-y-6">
                            @foreach ($jobs as $job)
                                <article>
                                    <p class="font-mono text-[11px] font-bold text-neutral-400 tabular-nums uppercase tracking-wider">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <h3 class="mt-1 font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-neutral-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-neutral-600">{{ $job['description'] }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- Højre kolonne: foto som prøveaftryk --}}
            <aside class="border-l border-neutral-200 pl-8">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full aspect-square object-cover grayscale contrast-125 border border-neutral-900">
                @endif
                @if ($skills)
                    <div class="mt-6">
                        <h2 class="font-mono text-[10px] font-bold uppercase tracking-[0.25em] text-neutral-900 border-t border-neutral-900 pt-2">Kompetencer</h2>
                        <div class="mt-3 space-y-1.5">
                            @foreach ($skills as $skill)
                                <p class="font-mono text-xs text-neutral-600 border-b border-dotted border-neutral-300 pb-1.5">{{ $skill }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if ($links->isNotEmpty())
                    <div class="mt-6">
                        <h2 class="font-mono text-[10px] font-bold uppercase tracking-[0.25em] text-neutral-900 border-t border-neutral-900 pt-2">Links</h2>
                        <div class="mt-3 space-y-1.5">
                            @foreach ($links as $link)
                                <p class="font-mono text-xs text-neutral-600 border-b border-dotted border-neutral-300 pb-1.5 break-all">
                                    <span class="font-bold text-neutral-900">{{ $link->name }}</span>
                                    <a href="{{ $link->url }}" class="underline hover:text-neutral-900">{{ $link->prettifyUrl() }}</a>
                                </p>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</body>
</html>