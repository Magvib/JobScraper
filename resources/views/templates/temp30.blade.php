{{-- Neon Cyberpunk --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ $user->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4; margin: 0; }
        @media print {
            body { background: white !important; padding: 0 !important; }
            .cv-page { box-shadow: none !important; margin: 0 !important; max-width: 100% !important; }
        }
        .cv-page { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .chromatic {
            text-shadow:
                -2px -1px 0 rgba(255, 0, 128, 0.8),
                 2px  1px 0 rgba(0, 229, 255, 0.8);
        }
        .glow-cyan { box-shadow: 0 0 12px rgba(34, 211, 238, 0.45); }
        .glow-pink { box-shadow: 0 0 12px rgba(236, 72, 153, 0.45); }
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-neutral-950 text-neutral-200 shadow-lg overflow-hidden min-h-[297mm] font-sans flex flex-col">

        {{-- Neon-header --}}
        <header class="px-12 pt-14 pb-10 border-b border-fuchsia-500/30 relative">
            <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-fuchsia-400">// netrunner_profile.exe</p>
            <div class="mt-4 flex items-end justify-between gap-8">
                <div>
                    <h1 class="chromatic text-5xl font-black uppercase tracking-tight text-white leading-none">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-3 text-lg text-cyan-300 font-medium">{{ $user->job_title }}</p>
                    @endif>
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 object-cover shrink-0 border-2 border-fuchsia-500 glow-pink saturate-150 contrast-125">
                @endif>
            </div>
            <div class="mt-6 flex flex-wrap gap-x-6 gap-y-1 text-xs text-neutral-400">
                @if ($user->phone)
                    <span><span class="text-cyan-400">TEL:</span> {{ $user->phone }}</span>
                @endif
                <span class="break-all"><span class="text-cyan-400">MAIL:</span> {{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span><span class="text-cyan-400">ADR:</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span><span class="text-cyan-400">FØDT:</span> {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>
        </header>

        {{-- Kompetencer som neon-chips --}}
        @if ($skills)
            <section class="px-12 py-5 flex flex-wrap items-center gap-2 border-b border-cyan-500/20">
                <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-cyan-400 mr-2">[mods]</span>
                @foreach ($skills as $skill)
                    <span class="text-xs font-bold text-fuchsia-300 border border-fuchsia-500/60 px-3 py-1 glow-pink">{{ $skill }}</span>
                @endforeach
            </section>
        @endif

        {{-- Erhvervserfaring som datakort --}}
        <main class="flex-1 px-12 py-10">
            @if ($jobs)
                <h2 class="text-[10px] font-bold uppercase tracking-[0.4em] text-cyan-400">// run_history.log</h2>
                <div class="mt-6 space-y-5">
                    @foreach ($jobs as $job)
                        <article class="border border-cyan-500/40 glow-cyan bg-cyan-500/5 px-5 py-4">
                            <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                <h3 class="font-bold text-lg text-white leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-xs font-bold text-cyan-300 uppercase tracking-widest whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    //
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'AKTIV' }}
                                </p>
                            </div>
                            <p class="mt-0.5 text-sm text-fuchsia-400">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-sm leading-relaxed text-neutral-300">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </main>

        <footer class="px-12 py-4 border-t border-fuchsia-500/30 flex justify-between text-[10px] uppercase tracking-[0.3em] text-neutral-500">
            <span class="text-fuchsia-500">▮▮▮ system online</span>
            <span>end_of_file</span>
        </footer>
    </div>
</body>
</html>