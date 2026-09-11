{{-- Game Boy Arcade --}}
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
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-stone-300 min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto shadow-lg min-h-[297mm] font-mono text-stone-900 px-10 py-8">

        {{-- Konsol-ramme --}}
        <div class="bg-stone-400 rounded-3xl border-4 border-stone-500 px-6 py-6 min-h-[280mm]">
            <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-stone-700">
                <span>CV-BOY</span>
                <span class="flex items-center gap-1">
                    <span class="w-2 h-2 bg-red-500 rounded-full inline-block"></span> PWR
                </span>
            </div>

            {{-- Skærm --}}
            <div class="mt-3 bg-stone-800 rounded-xl px-4 py-4 border-b-8 border-stone-600">
                <div class="bg-[#9bbc0f] text-[#0f380f] px-4 py-5 min-h-[200mm] border-2 border-[#0f380f]">

                    {{-- Titel-skærm --}}
                    <p class="text-center text-[10px] font-bold uppercase tracking-[0.4em]">▶ PLAYER 1</p>
                    <h1 class="mt-3 text-center text-3xl font-bold uppercase tracking-widest leading-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-center text-xs font-bold uppercase">{{ $user->job_title }}</p>
                    @endif
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}"
                             class="mt-4 mx-auto w-20 h-20 object-cover border-4 border-[#0f380f] pixelated">
                    @endif

                    {{-- Kontakt som statusbar --}}
                    <div class="mt-4 text-[10px] leading-5">
                        <p>TEL: {{ $user->phone ?? '--------' }}</p>
                        <p class="break-all">MAIL: {{ $user->email }}</p>
                        @if ($user->address || $user->city)
                            <p>ADR: {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                        @endif
                        @if ($user->birthdate)
                            <p>FØDT: {{ $user->birthdate->format('d/m/Y') }}</p>
                        @endif
                    </div>

                    {{-- Erhvervserfaring som levels --}}
                    @if ($jobs)
                        <p class="mt-5 text-[10px] font-bold uppercase tracking-[0.3em] border-y-2 border-dashed border-[#0f380f] py-1">★ LEVEL LOG ★</p>
                        <div class="mt-3 space-y-4 text-[11px]">
                            @foreach ($jobs as $i => $job)
                                <div>
                                    <p class="font-bold">LEVEL {{ $i + 1 }} — {{ $job['title'] }}</p>
                                    <p>{{ $job['company'] }}</p>
                                    <p class="text-[10px]">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} → {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'KØRER' }}</p>
                                    @if (!empty($job['description']))
                                        <p class="mt-1 text-[10px] leading-4">{{ $job['description'] }}</p>
                                    @endif
                                    <p class="mt-1">XP: {{ str_repeat('█', 6) }}{{ str_repeat('░', 4) }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Kompetencer som inventory --}}
                    @if ($skills)
                        <p class="mt-5 text-[10px] font-bold uppercase tracking-[0.3em] border-y-2 border-dashed border-[#0f380f] py-1">◆ INVENTORY ◆</p>
                        <div class="mt-3 flex flex-wrap gap-2 text-[10px]">
                            @foreach ($skills as $skill)
                                <span class="border-2 border-[#0f380f] px-2 py-1 font-bold">[{{ $skill }}]</span>
                            @endforeach
                        </div>
                    @endif

                    <p class="mt-6 text-center text-[10px] font-bold tracking-widest">PRESS START ▶▶</p>
                </div>
            </div>

            {{-- Kontroller --}}
            <div class="mt-5 flex justify-between items-center px-4">
                <div class="grid grid-cols-3 gap-0.5 w-20 h-20">
                    <span></span><span class="bg-stone-700 rounded-sm"></span><span></span>
                    <span class="bg-stone-700 rounded-sm"></span><span class="bg-stone-700 rounded-full w-4 h-4 place-self-center"></span><span class="bg-stone-700 rounded-sm"></span>
                    <span></span><span class="bg-stone-700 rounded-sm"></span><span></span>
                </div>
                <p class="text-[10px] font-bold text-stone-700 uppercase tracking-widest">A ▮ B</p>
            </div>
        </div>
    </div>
</body>
</html>