{{-- Cinema Ticket --}}
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
        .ticket-perforation {
            background-image: radial-gradient(circle at 1px 3px, #f8fafc 2.5px, transparent 3px);
            background-size: 10px 10px;
            background-repeat: repeat-y;
        }
    </style>
</head>
@php
    $user = auth()->user();
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-red-950 min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto shadow-lg min-h-[297mm] font-sans text-red-950 px-12 py-12 flex flex-col justify-center">

        {{-- Billetholder --}}
        <div class="bg-red-900 rounded-3xl border-4 border-red-800 p-8 shadow-inner">
            <div class="flex items-center justify-between px-4 pb-5 text-amber-300">
                <p class="text-sm font-bold uppercase tracking-[0.4em]">Biografen</p>
                <p class="text-[10px] uppercase tracking-[0.3em] text-amber-200/70">Sæson {{ now()->format('Y') }}</p>
            </div>

            {{-- Selve billetten --}}
            <div class="bg-amber-50 rounded-xl overflow-hidden flex shadow-xl">

                {{-- Stub med ADMIT ONE --}}
                <div class="ticket-perforation bg-amber-100 w-[52mm] shrink-0 border-r-4 border-dashed border-amber-300 px-5 py-8 flex flex-col items-center text-center gap-4">
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}"
                             class="w-24 h-24 rounded-full object-cover border-4 border-red-800">
                    @endif
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-red-700">Admit One</p>
                        <p class="mt-2 text-lg font-black leading-tight">{{ $user->name }}</p>
                        @if ($user->job_title)
                            <p class="mt-1 text-xs text-red-700 font-semibold">{{ $user->job_title }}</p>
                        @endif
                    </div>
                    <div class="text-[10px] text-red-800/70 leading-4">
                        <p>SÆDE: 1 · RÆKKE: A</p>
                        <p>SAL: JOBMARKED</p>
                        @if ($user->birthdate)
                            <p>FØDT: {{ $user->birthdate->format('d/m/Y') }}</p>
                        @endif
                    </div>
                </div>

                {{-- Hoveddel --}}
                <div class="flex-1 px-8 py-8">
                    <div class="flex justify-between items-baseline border-b-2 border-red-800 pb-3">
                        <p class="text-xs font-bold uppercase tracking-[0.3em] text-red-700">Forestilling: Karrieren</p>
                        <p class="text-xs font-bold text-red-700">{{ $user->phone ?? '' }}</p>
                    </div>
                    <p class="mt-3 text-xs text-red-800/80 break-all">{{ $user->email }}@if ($user->address || $user->city) · {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}@endif</p>

                    {{-- Showtimes = jobs --}}
                    @if ($jobs)
                        <p class="mt-6 text-[10px] font-bold uppercase tracking-[0.3em] text-red-700 border-b border-red-300 pb-1">Spilleplan — Erhvervserfaring &amp; Uddannelse</p>
                        <div class="mt-4 space-y-4">
                            @foreach ($jobs as $job)
                                <div class="grid grid-cols-[42mm_1fr] gap-4">
                                    <div class="text-center bg-red-800 text-amber-50 rounded-lg py-2 self-start">
                                        <p class="text-[10px] uppercase tracking-widest">Visning</p>
                                        <p class="text-sm font-bold">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}</p>
                                        <p class="text-[10px]">{{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'KØRER NU' }}</p>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-base leading-snug">{{ $job['title'] }}</h3>
                                        <p class="text-sm text-red-700">{{ $job['company'] }}</p>
                                        @if (!empty($job['description']))
                                            <p class="mt-1 text-xs leading-relaxed text-red-950/75">{{ $job['description'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Kompetencer --}}
                    @if ($skills)
                        <p class="mt-6 text-[10px] font-bold uppercase tracking-[0.3em] text-red-700 border-b border-red-300 pb-1">Medvirkende — Kompetencer</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($skills as $skill)
                                <span class="text-xs font-bold bg-red-800 text-amber-50 px-3 py-1 rounded-full">★ {{ $skill }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <p class="mt-6 text-center text-[10px] uppercase tracking-[0.4em] text-amber-200/60">Tak fordi du valgte biografen · God fornøjelse</p>
        </div>
    </div>
</body>
</html>