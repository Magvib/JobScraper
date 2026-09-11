{{-- Windows 95 Desktop --}}
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
        .win-out { border: 2px solid; border-color: #ffffff #404040 #404040 #ffffff; }
        .win-in { border: 2px solid; border-color: #808080 #dfdfdf #dfdfdf #808080; }
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-neutral-300 min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-teal-800 shadow-lg min-h-[297mm] font-sans text-black px-6 py-6 flex flex-col">

        {{-- Programvindue --}}
        <div class="bg-[#c0c0c0] win-out p-0.5 flex-1 flex flex-col">
            {{-- Titellinje --}}
            <div class="bg-blue-900 text-white px-2 py-1 flex items-center justify-between">
                <p class="text-sm font-bold">💾 C:/karriere/cv.exe</p>
                <div class="flex gap-1 text-[10px] font-bold">
                    <span class="win-out bg-[#c0c0c0] text-black px-2 leading-4">_</span>
                    <span class="win-out bg-[#c0c0c0] text-black px-2 leading-4">□</span>
                    <span class="win-out bg-[#c0c0c0] text-black px-2 leading-4">✕</span>
                </div>
            </div>

            {{-- Menulinje --}}
            <div class="px-2 py-1 text-xs flex gap-4 border-b border-neutral-400">
                <span class="font-bold underline">F</span>il <span class="font-bold underline">R</span>ediger <span class="font-bold underline">V</span>is <span class="font-bold underline">H</span>jælp
            </div>

            {{-- Adressefelt --}}
            <div class="m-2 win-out bg-white win-in px-2 py-1 text-xs flex items-center justify-between">
                <span class="truncate">{{ $user->email }} · {{ $user->phone ?? '—' }}@if ($user->address || $user->city) · {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}@endif @if ($user->birthdate) · Født {{ $user->birthdate->format('d/m/Y') }}@endif</span>
                <span class="bg-[#c0c0c0] win-out text-[9px] px-1.5 py-0.5 shrink-0 ml-2">↵</span>
            </div>

            <div class="flex-1 flex gap-2 px-2 pb-2">
                {{-- Indhold --}}
                <div class="flex-1 win-out bg-white win-in p-3">
                    <div class="flex items-center gap-3 border-b-2 border-neutral-300 pb-2">
                        @if ($photo)
                            <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-16 h-16 object-cover shrink-0">
                        @endif
                        <div>
                            <h1 class="text-xl font-bold">{{ $user->name }}</h1>
                            @if ($user->job_title)
                                <p class="text-xs text-neutral-600">{{ $user->job_title }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Erhvervserfaring som listview --}}
                    @if ($jobs)
                        <div class="mt-3 win-out bg-white win-in">
                            <div class="grid grid-cols-[36mm_1fr_30mm] text-[10px] font-bold bg-[#c0c0c0] border-b-2 border-neutral-300">
                                <p class="px-2 py-1 border-r border-neutral-400">Startdato</p>
                                <p class="px-2 py-1 border-r border-neutral-400">Stilling</p>
                                <p class="px-2 py-1">Slutdato</p>
                            </div>
                            @foreach ($jobs as $job)
                                <div class="grid grid-cols-[36mm_1fr_30mm] text-xs border-b border-neutral-200">
                                    <p class="px-2 py-1.5 border-r border-neutral-200">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}</p>
                                    <p class="px-2 py-1.5 border-r border-neutral-200">
                                        <span class="font-bold">{{ $job['title'] }}</span> <span class="text-neutral-500">— {{ $job['company'] }}</span>
                                        @if (!empty($job['description']))<span class="block text-neutral-600 leading-4 text-[10px]">{{ $job['description'] }}</span>@endif
                                    </p>
                                    <p class="px-2 py-1.5">{{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'Kører...' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Statusbar --}}
                    <div class="mt-2 flex justify-between text-[10px]">
                        <span class="win-out bg-[#c0c0c0] px-2 py-0.5">{{ count($jobs) }} objekt(er)</span>
                        <span class="win-out bg-[#c0c0c0] px-2 py-0.5">100 % erfaring</span>
                    </div>
                </div>

                {{-- Sidepanel: kompetencer --}}
                @if ($skills)
                    <div class="w-[46mm] shrink-0 flex flex-col gap-2">
                        <div class="win-out bg-[#c0c0c0] win-in p-2">
                            <p class="text-xs font-bold mb-1">☑ Kompetencer</p>
                            <div class="space-y-1 text-[10px]">
                                @foreach ($skills as $skill)
                                    <p class="flex items-center gap-1.5"><span class="win-out bg-white w-3 h-3 inline-block shrink-0"></span> {{ $skill }}</p>
                                @endforeach
                            </div>
                        </div>
                        <div class="win-out bg-[#c0c0c0] win-in p-2 text-[10px]">
                            <p class="font-bold mb-1">Kapacitet</p>
                            <div class="win-out bg-white win-in h-3 relative">
                                <div class="absolute inset-y-0 left-0 bg-blue-900 w-[88%]"></div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Proceslinje --}}
        <div class="mt-2 bg-[#c0c0c0] win-out px-2 py-1 flex items-center justify-between text-xs">
            <span class="win-out bg-[#c0c0c0] px-2 py-0.5 font-bold">Start ⊞</span>
            <div class="flex gap-1.5">
                <span class="win-out bg-[#c0c0c0] px-2 py-0.5">📁 CV</span>
                <span class="win-out bg-[#c0c0c0] px-2 py-0.5">💼 Job</span>
            </div>
            <span class="win-out bg-[#c0c0c0] px-2 py-0.5">🕐 {{ now()->format('H:i') }}</span>
        </div>
    </div>
</body>
</html>