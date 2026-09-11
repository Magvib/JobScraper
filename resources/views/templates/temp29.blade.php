{{-- Financial Report --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ auth()->user()->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4; margin: 0; }
        @media print {
            body { background: white !important; padding: 0 !important; }
            .cv-page { box-shadow: none !important; margin: 0 !important; max-width: 100% !important; }
        }
        .cv-page { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    </style>
</head>
@php
    $user = auth()->user();
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
    $firstJob = collect($jobs)->filter(fn ($j) => !empty($j['startDate']))->sortBy('startDate')->first();
    $years = $firstJob ? max(0, \Carbon\Carbon::parse($firstJob['startDate'])->diffInYears(now())) : 0;
@endphp
<body class="bg-slate-200 min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-mono text-slate-800 px-12 py-10 flex flex-col">

        {{-- Rapportheader --}}
        <header class="flex items-start justify-between border-b-4 border-slate-800 pb-4">
            <div>
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-400">Årsrapport · Regnskabsår {{ now()->format('Y') }}</p>
                <h1 class="mt-2 text-3xl font-bold uppercase tracking-widest text-slate-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1 text-xs text-slate-500">Afdeling: {{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-20 h-20 object-cover grayscale shrink-0">
            @endif
        </header>

        {{-- KPI-tiles --}}
        <div class="mt-6 grid grid-cols-4 border border-slate-300 divide-x divide-slate-300">
            <div class="px-4 py-4 text-center">
                <p class="text-[9px] uppercase tracking-widest text-slate-400">Erfaring</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($years, 1) }}</p>
                <p class="text-[9px] text-slate-400">ÅR</p>
            </div>
            <div class="px-4 py-4 text-center">
                <p class="text-[9px] uppercase tracking-widest text-slate-400">Poster</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ str_pad(count($jobs), 2, '0', STR_PAD_LEFT) }}</p>
                <p class="text-[9px] text-slate-400">JOBS</p>
            </div>
            <div class="px-4 py-4 text-center">
                <p class="text-[9px] uppercase tracking-widest text-slate-400">Aktiver</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ str_pad(count($skills), 2, '0', STR_PAD_LEFT) }}</p>
                <p class="text-[9px] text-slate-400">SKILLS</p>
            </div>
            <div class="px-4 py-4 text-center">
                <p class="text-[9px] uppercase tracking-widest text-slate-400">Status</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">▲</p>
                <p class="text-[9px] text-slate-400">VÆKST</p>
            </div>
        </div>

        {{-- Kontakt som tabelfod --}}
        <p class="mt-3 text-[10px] text-slate-500 flex flex-wrap gap-x-5">
            <span>TEL {{ $user->phone ?? '—' }}</span>
            <span class="break-all">MAIL {{ $user->email }}</span>
            @if ($user->address || $user->city)<span>ADR {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>@endif
            @if ($user->birthdate)<span>FØDT {{ $user->birthdate->format('d/m/Y') }}</span>@endif
        </p>

        {{-- Erhvervserfaring som hovedbog --}}
        @if ($jobs)
            <section class="mt-8 flex-1">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-900 border-b-2 border-slate-800 pb-2">Note 1 — Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-4 divide-y divide-dotted divide-slate-300">
                    @foreach ($jobs as $job)
                        <div class="py-4 grid grid-cols-[1fr_40mm] gap-6">
                            <div>
                                <h3 class="font-bold text-base">{{ $job['title'] }}</h3>
                                <p class="text-xs text-slate-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-1.5 text-xs leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </div>
                            <div class="text-right text-xs">
                                <p class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}</p>
                                <p class="text-slate-400">{{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'løbende' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Kompetencer som aktivliste --}}
        @if ($skills)
            <section class="mt-8 border-t-4 border-double border-slate-800 pt-4">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-900">Note 2 — Kompetencer (immaterielle aktiver)</h2>
                <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                    @foreach ($skills as $i => $skill)
                        <p class="border border-slate-300 px-3 py-1.5">
                            <span class="text-slate-400">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}.</span> {{ $skill }}
                        </p>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Revisionspåtegning --}}
        <footer class="mt-8 border-t border-slate-300 pt-3 text-[9px] uppercase tracking-widest text-slate-400 flex justify-between">
            <span>Kontoplan: CV-{{ strtoupper(preg_replace('/[^a-z0-9]/i', '', $user->name ?? '')) }}</span>
            <span>Revideret: {{ now()->format('d.m.Y') }}</span>
        </footer>
    </div>
</body>
</html>