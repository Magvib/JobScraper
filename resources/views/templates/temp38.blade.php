{{-- Music Playlist --}}
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
    $photo = $user->image ? '/storage/' . $user->image : $user->avatar;
@endphp
<body class="bg-neutral-800 min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#121212] text-neutral-200 shadow-lg min-h-[297mm] font-sans px-12 py-12 flex flex-col">

        {{-- Playliste-header --}}
        <header class="flex items-end gap-7">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-36 h-36 object-cover shadow-2xl shrink-0">
            @else
                <div class="w-36 h-36 bg-neutral-800 shadow-2xl shrink-0 flex items-center justify-center text-5xl text-neutral-600">♪</div>
            @endif
            <div class="pb-1">
                <p class="text-[10px] font-bold uppercase tracking-[0.3em]">Playliste</p>
                <h1 class="mt-2 text-5xl font-black tracking-tight text-white leading-none">This Is {{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-sm text-neutral-400">{{ $user->job_title }} · {{ now()->format('Y') }}</p>
                @endif
            </div>
        </header>

        {{-- Kontaktrække som nu-spiller --}}
        <div class="mt-8 bg-neutral-900/80 rounded-lg px-5 py-4 flex items-center gap-4">
            <span class="w-10 h-10 rounded-full bg-green-500 text-black flex items-center justify-center text-lg font-bold shrink-0">▶</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ $user->name }}@if ($user->job_title) — {{ $user->job_title }}@endif</p>
                <p class="text-xs text-neutral-400 truncate">{{ collect([$user->phone, $user->email])->filter()->implode(' · ') }}@if ($user->address || $user->city) · {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}@endif @if ($user->birthdate) · Født {{ $user->birthdate->format('d/m/Y') }}@endif</p>
                <div class="mt-2 h-1 bg-neutral-700 rounded-full">
                    <div class="h-full w-2/3 bg-green-500 rounded-full"></div>
                </div>
            </div>
        </div>

        {{-- Sporliste: erhvervserfaring --}}
        @if ($jobs)
            <div class="mt-8 flex-1">
                <div class="grid grid-cols-[10mm_1fr_60mm_24mm] gap-3 text-[10px] uppercase tracking-[0.25em] text-neutral-500 border-b border-neutral-800 pb-2 px-2">
                    <p>#</p>
                    <p>Titel</p>
                    <p>Album</p>
                    <p class="text-right">Længde</p>
                </div>
                <div class="divide-y divide-neutral-800/60">
                    @foreach ($jobs as $i => $job)
                        <div class="grid grid-cols-[10mm_1fr_60mm_24mm] gap-3 items-center px-2 py-3 hover:bg-neutral-900
                                    {{ empty($job['endDate']) ? 'bg-green-500/5' : '' }}">
                            <p class="text-sm {{ empty($job['endDate']) ? 'text-green-500' : 'text-neutral-500' }}">
                                {{ empty($job['endDate']) ? '▶' : $i + 1 }}
                            </p>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-white leading-tight">{{ $job['title'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-xs text-neutral-400 leading-snug mt-0.5">{{ $job['description'] }}</p>
                                @endif
                            </div>
                            <p class="text-xs text-neutral-400 truncate">{{ $job['company'] }}</p>
                            <p class="text-xs text-neutral-400 text-right tabular-nums whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                –
                                {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Kompetencer som kunstnere, man følger --}}
        @if ($skills)
            <footer class="mt-8 border-t border-neutral-800 pt-5">
                <p class="text-[10px] uppercase tracking-[0.25em] text-neutral-500 mb-3">Følgende kompetencer</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-xs font-semibold text-neutral-800 bg-green-500 rounded-full px-4 py-1.5">{{ $skill }}</span>
                    @endforeach
                </div>
            </footer>
        @endif
    </div>
</body>
</html>