{{-- Vinyl Record --}}
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
        /* Grammofonplade: riller via koncentriske radiale streger */
        .grooves {
            background:
                repeating-radial-gradient(circle at center, transparent 0 5px, rgba(255,255,255,.05) 5px 6px),
                radial-gradient(circle at center, #1c1917 0%, #292524 40%, #1c1917 100%);
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#1c1917] text-stone-100 shadow-xl min-h-[297mm] font-sans overflow-hidden">

        <div class="px-14 pt-14 pb-10">
            <div class="flex items-center gap-12">
                {{-- Pladen med centeretiket --}}
                <div class="grooves shrink-0 w-52 h-52 rounded-full flex items-center justify-center relative">
                    <div class="w-24 h-24 rounded-full bg-amber-400 flex flex-col items-center justify-center text-center shadow-inner px-2">
                        <p class="text-[8px] font-black uppercase tracking-widest text-amber-900 leading-tight">Karriere·LP</p>
                        <p class="text-[9px] font-bold text-amber-950 leading-tight mt-1 truncate w-full">{{ $user->name }}</p>
                    </div>
                    <span class="absolute w-3 h-3 rounded-full bg-[#1c1917] ring-1 ring-stone-600"></span>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-amber-400">Side A · Curriculum Vitae</p>
                    <h1 class="mt-4 text-5xl font-extrabold tracking-tight leading-none text-white">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-3 text-lg font-semibold text-amber-300">{{ $user->job_title }}</p>
                    @endif
                    <p class="mt-6 text-sm text-stone-400">
                        {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
                    </p>
                </div>
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="mt-10 w-24 h-24 object-cover rounded-full ring-4 ring-stone-700 grayscale-20">
            @endif
        </div>

        {{-- Trackliste som sideindhold --}}
        <div class="bg-[#f5f0e6] text-stone-900 mx-0 px-14 py-10 min-h-[170mm]">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-xs font-black uppercase tracking-[0.35em] text-amber-700 flex items-baseline justify-between border-b-2 border-stone-900 pb-2">
                        <span>Ansøgning</span><span class="font-mono text-stone-400 normal-case tracking-normal">33⅓ rpm</span>
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-xs font-black uppercase tracking-[0.35em] text-amber-700 flex items-baseline justify-between border-b-2 border-stone-900 pb-2">
                        <span>Erhvervserfaring &amp; uddannelse</span><span class="font-mono text-stone-400 normal-case tracking-normal">Tracklist</span>
                    </h2>
                    <div class="mt-5">
                        @foreach ($jobs as $i => $job)
                            <article class="flex items-baseline gap-5 border-b border-stone-300 py-3.5">
                                <span class="font-mono text-sm font-bold text-amber-700 tabular-nums shrink-0">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="flex-1">
                                    <h3 class="font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-stone-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-1.5 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                                <span class="font-mono text-[11px] text-stone-400 tabular-nums whitespace-nowrap shrink-0">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-8">
                    <h2 class="text-xs font-black uppercase tracking-[0.35em] text-amber-700 border-b-2 border-stone-900 pb-2">Kompetencer · B-siden</h2>
                    <div class="mt-4 flex flex-wrap gap-x-6 gap-y-1.5 text-[14px] font-medium text-stone-600">
                        @foreach ($skills as $skill)
                            <span>{{ $skill }} <span class="text-amber-600">·</span></span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-10 flex justify-between text-[10px] uppercase tracking-[0.3em] text-stone-400 font-bold">
                <span>{{ $user->name }}</span>
                <span>Stereo · {{ now()->format('Y') }}</span>
            </footer>
        </div>
    </div>
</body>
</html>