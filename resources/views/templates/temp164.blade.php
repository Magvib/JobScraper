{{-- Circuit Board --}}
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
        /* Printbaner som kredsløbssporen med 45°-knæk */
        .trace {
            position: relative;
            height: 2px;
            background: #15803d;
        }
        .trace::before, .trace::after {
            content: '';
            position: absolute;
            width: 8px; height: 8px;
            border-radius: 9999px;
            background: #166534;
            box-shadow: 0 0 0 3px rgba(21, 128, 61, .25);
        }
        .trace::before { left: -4px; top: -3px; }
        .trace::after  { right: -4px; top: -3px; }
        /* Grønt PCB-raster */
        .pcb {
            background-image:
                linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
            background-size: 22px 22px;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-green-950 text-green-50 shadow-xl min-h-[297mm] font-sans overflow-hidden">

        <header class="pcb px-14 pt-14 pb-9 border-b-2 border-green-400/40 relative">
            <div class="flex items-center gap-8">
                @if ($photo)
                    <div class="shrink-0 border border-green-400/60 bg-green-900 p-1.5 shadow-[0_0_18px_rgba(74,222,128,.25)]">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover rounded-sm">
                    </div>
                @endif
                <div>
                    <p class="font-mono text-[10px] uppercase tracking-[0.4em] text-green-400">system // profil v{{ now()->format('Y') }}</p>
                    <h1 class="mt-2 text-4xl font-extrabold tracking-tight text-green-50">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-base font-semibold text-green-300">{{ $user->job_title }}</p>
                    @endif
                </div>
                {{-- Chip-mærke i hjørnet --}}
                <span class="ml-auto shrink-0 font-mono text-[9px] uppercase tracking-widest text-green-300/70 border border-green-400/40 px-2.5 py-1.5 rotate-90 origin-center">IC · {{ collect($skills ?? [])->count() ?: '0' }} pins</span>
            </div>
            <p class="mt-6 font-mono text-xs text-green-200/80">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </p>
        </header>

        <div class="px-14 py-9">
            @if (isset($coverLetter))
                <section>
                    <h2 class="font-mono text-xs font-bold uppercase tracking-[0.3em] text-green-400 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400 shadow-[0_0_8px_rgba(74,222,128,.8)]"></span>ansoegning.txt
                    </h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-green-100/90">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="font-mono text-xs font-bold uppercase tracking-[0.3em] text-green-400 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400 shadow-[0_0_8px_rgba(74,222,128,.8)]"></span>erfaring.log
                    </h2>
                    <div class="mt-7 space-y-8">
                        @foreach ($jobs as $job)
                            <article class="relative pl-9">
                                {{-- Sporen ud til hvert punkt --}}
                                <span class="trace absolute left-0 top-2.5 w-6"></span>
                                <span class="absolute left-0 top-0 w-2.5 h-5 border-l border-t border-b border-green-400/50"></span>
                                <p class="font-mono text-[11px] font-bold tracking-widest text-green-400 tabular-nums uppercase">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-bold text-lg leading-snug text-green-50">{{ $job['title'] }}</h3>
                                <p class="text-sm font-medium text-green-300/90">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-green-100/70">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="font-mono text-xs font-bold uppercase tracking-[0.3em] text-green-400 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400 shadow-[0_0_8px_rgba(74,222,128,.8)]"></span>kompetencer.pins
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        @foreach ($skills as $skill)
                            <span class="font-mono text-[13px] font-semibold text-green-100 bg-green-900/80 border border-green-500/50 rounded-sm px-3.5 py-1.5 shadow-[0_0_10px_rgba(21,128,61,.4)]">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-12 pt-4 border-t border-green-400/30 flex justify-between font-mono text-[10px] uppercase tracking-[0.25em] text-green-500">
                <span>{{ $user->name }}</span>
                <span>power: ok · {{ now()->format('H:i') }}</span>
            </footer>
        </div>
    </div>
</body>
</html>