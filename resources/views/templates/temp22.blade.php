{{-- Cassette Mixtape --}}
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
<body class="bg-neutral-200 min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto shadow-lg min-h-[297mm] font-mono text-neutral-800 px-12 py-10 flex flex-col items-center justify-center">

        {{-- Kassettekrop --}}
        <div class="w-full bg-zinc-700 rounded-2xl border-4 border-zinc-800 px-10 pt-10 pb-8 relative">

            {{-- Ruller --}}
            <div class="flex justify-between mx-16">
                <div class="w-24 h-24 rounded-full border-8 border-zinc-500 bg-zinc-200 flex items-center justify-center">
                    <div class="w-8 h-8 rounded-full bg-zinc-400 border-4 border-zinc-600"></div>
                </div>
                <div class="w-24 h-24 rounded-full border-8 border-zinc-500 bg-zinc-200 flex items-center justify-center">
                    <div class="w-8 h-8 rounded-full bg-zinc-400 border-4 border-zinc-600"></div>
                </div>
            </div>

            {{-- Båndvindue --}}
            <div class="mx-20 -mt-2 h-3 bg-zinc-200 border-x-4 border-zinc-500"></div>

            {{-- Etiket: SIDE A --}}
            <div class="mt-6 bg-amber-100 border-2 border-zinc-800 px-7 py-6 relative">
                <span class="absolute top-2.5 left-3 w-4 h-1.5 bg-zinc-800 rounded-full"></span>
                <span class="absolute top-2.5 right-3 w-4 h-1.5 bg-zinc-800 rounded-full"></span>
                <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-zinc-500">Kassette — Side A · Erhvervserfaring</p>
                <div class="mt-2 flex items-center gap-5">
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}"
                             class="w-16 h-16 object-cover border-2 border-zinc-800 shrink-0">
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold uppercase tracking-widest leading-tight">{{ $user->name }}</h1>
                        @if ($user->job_title)
                            <p class="mt-1 text-xs text-zinc-600">{{ $user->job_title }}</p>
                        @endif
                    </div>
                </div>
                <div class="mt-4 text-[10px] text-zinc-600 leading-5">
                    <p>Tlf {{ $user->phone ?? '—' }} · {{ $user->email }}@if ($user->address || $user->city) · {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}@endif @if ($user->birthdate) · Født {{ $user->birthdate->format('d/m/Y') }}@endif</p>
                </div>
                <div class="mt-4 space-y-3">
                    @foreach ($jobs as $job)
                        <div class="grid grid-cols-[30mm_1fr] gap-3 text-xs">
                            <p class="text-zinc-500">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                –
                                {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <div>
                                <p class="font-bold">{{ $job['title'] }} <span class="text-zinc-500 font-normal">— {{ $job['company'] }}</span></p>
                                @if (!empty($job['description']))
                                    <p class="text-zinc-600 leading-4">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Etiket: SIDE B --}}
            @if ($skills)
                <div class="mt-4 bg-amber-100 border-2 border-zinc-800 px-7 py-5">
                    <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-zinc-500">Side B · Kompetencer</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-xs font-bold border border-zinc-800 px-3 py-1 bg-white/60">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Skruer og tekst --}}
            <div class="mt-6 flex justify-between items-center text-[10px] uppercase tracking-[0.3em] text-zinc-400">
                <span>90 min · CrO₂</span>
                <span class="flex gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-zinc-400 border border-zinc-500 inline-block"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-zinc-400 border border-zinc-500 inline-block"></span>
                </span>
                <span>Side A ↑</span>
            </div>
        </div>
    </div>
</body>
</html>