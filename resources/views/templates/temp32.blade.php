{{-- Duotone Sport --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ $user->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .cv-page { box-shadow: none !important; margin: 0 !important; max-width: 100% !important; }
        }
        .cv-page { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .slash-a { clip-path: polygon(0 0, 100% 0, 86% 100%, 0 100%); }
        .slash-b { clip-path: polygon(100% 0, 100% 100%, 76% 100%, 96% 0); }
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg overflow-hidden min-h-[297mm] font-sans text-gray-900 flex flex-col">

        {{-- Trøje-header med diagonale striber --}}
        <header class="relative bg-blue-900 text-white min-h-[62mm]">
            <span class="absolute inset-0 bg-orange-500 slash-a"></span>
            <span class="absolute inset-0 bg-white/10 slash-b"></span>
            <div class="relative px-12 py-10 flex items-center justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.35em] text-orange-300">Hold CV · Sæson {{ now()->format('Y') }}</p>
                    <h1 class="mt-2 text-5xl font-black italic uppercase tracking-tight leading-none">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-lg font-bold text-orange-300 uppercase">{{ $user->job_title }}</p>
                    @endif
                </div>
                <div class="text-right shrink-0">
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}"
                             class="w-24 h-24 object-cover border-4 border-orange-500 mb-2 ml-auto">
                    @endif
                    <p class="text-6xl font-black italic text-white leading-none">#1</p>
                </div>
            </div>
        </header>

        {{-- Resultattavle-kontakt --}}
        <div class="bg-neutral-100 px-12 py-3 flex flex-wrap gap-x-6 gap-y-1 text-xs font-bold uppercase tracking-wider text-blue-900">
            @if ($user->phone)
                <span>{{ $user->phone }}</span>
            @endif
            <span class="break-all">{{ $user->email }}</span>
            @if ($user->address || $user->city)
                <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)
                <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </div>

        {{-- Erhvervserfaring som kampprogram --}}
        <main class="flex-1 px-12 py-10">
            @if ($jobs)
                <h2 class="text-sm font-black uppercase tracking-[0.3em] text-orange-500 italic">Kampprogram — Erfaring &amp; Uddannelse</h2>
                <div class="mt-6 space-y-5">
                    @foreach ($jobs as $i => $job)
                        <article class="flex items-stretch gap-5">
                            <div class="shrink-0 w-14 text-center bg-blue-900 text-white py-2 flex flex-col justify-center">
                                <p class="text-2xl font-black italic leading-none">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</p>
                                <p class="text-[8px] uppercase tracking-widest">Runde</p>
                            </div>
                            <div class="flex-1 border-b-2 border-neutral-200 pb-4
                                        {{ $i % 2 === 0 ? 'border-l-4 border-l-orange-500 pl-4' : 'border-r-4 border-r-blue-900 pr-4 text-right' }}">
                                <p class="text-[10px] font-black uppercase tracking-widest text-orange-500">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-0.5 text-lg font-black italic uppercase leading-tight">{{ $job['title'] }}</h3>
                                <p class="text-sm font-bold text-blue-900">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            {{-- Kompetencer som truppen --}}
            @if ($skills)
                <h2 class="mt-10 text-sm font-black uppercase tracking-[0.3em] text-orange-500 italic">Truppen — Kompetencer</h2>
                <div class="mt-5 flex flex-wrap gap-2.5">
                    @foreach ($skills as $skill)
                        <span class="text-xs font-black uppercase text-white bg-blue-900 border-b-4 border-orange-500 px-4 py-2">{{ $skill }}</span>
                    @endforeach
                </div>
            @endif
        </main>

        <footer class="bg-blue-900 px-12 py-3 flex justify-between text-[10px] font-black uppercase tracking-[0.3em] text-orange-300">
            <span>{{ $user->name }}</span>
            <span>Fuld tid · 100%</span>
        </footer>
    </div>
</body>
</html>