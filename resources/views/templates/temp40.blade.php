{{-- Departure Board --}}
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
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-neutral-700 min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-neutral-900 shadow-lg overflow-hidden min-h-[297mm] font-mono text-amber-400 flex flex-col">

        {{-- Lufthavns-skilt --}}
        <header class="bg-black border-b-4 border-amber-400/30 px-10 py-8 flex items-center justify-between gap-6">
            <div>
                <p class="text-[10px] uppercase tracking-[0.5em] text-amber-400/60">Afgange · Departures</p>
                <h1 class="mt-3 text-4xl font-bold uppercase tracking-[0.15em] leading-none">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-3 text-sm uppercase tracking-[0.3em] text-amber-400/80">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover border-2 border-amber-400/60 shrink-0 grayscale contrast-125">
            @endif
        </header>

        {{-- Passagerinfo --}}
        <div class="bg-black border-b border-amber-400/20 px-10 py-3 text-xs text-amber-400/70 flex flex-wrap gap-x-6 gap-y-1 uppercase tracking-widest">
            @if ($user->phone)
                <span>Tlf {{ $user->phone }}</span>
            @endif
            <span class="break-all">Mail {{ $user->email }}</span>
            @if ($user->address || $user->city)
                <span>Gate {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)
                <span>Id {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </div>

        {{-- Afgangstavle: erhvervserfaring --}}
        @if ($jobs)
            <main class="flex-1 px-10 py-8">
                <div class="grid grid-cols-[26mm_1fr_40mm_24mm] gap-3 text-[10px] uppercase tracking-[0.25em] text-amber-400/50 border-b-2 border-amber-400/25 pb-2">
                    <p>Tid</p>
                    <p>Destination</p>
                    <p>Selskab</p>
                    <p class="text-right">Status</p>
                </div>
                <div class="divide-y divide-amber-400/15">
                    @foreach ($jobs as $job)
                        <div class="grid grid-cols-[26mm_1fr_40mm_24mm] gap-3 items-start py-4">
                            <div>
                                <p class="text-sm font-bold">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}</p>
                                <p class="text-xs text-amber-400/50">{{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : '—' }}</p>
                            </div>
                            <div>
                                <p class="text-base font-bold uppercase tracking-wide leading-tight">{{ $job['title'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-1 text-xs leading-relaxed text-amber-400/60">{{ $job['description'] }}</p>
                                @endif
                            </div>
                            <p class="text-sm text-amber-400/80 pt-0.5">{{ $job['company'] }}</p>
                            <p class="text-right pt-0.5">
                                @if (empty($job['endDate']))
                                    <span class="inline-block text-[10px] font-bold uppercase tracking-widest bg-green-500 text-black px-2 py-1">Boarding</span>
                                @else
                                    <span class="inline-block text-[10px] font-bold uppercase tracking-widest border border-amber-400/50 px-2 py-1">Ankommet</span>
                                @endif
                            </p>
                        </div>
                    @endforeach
                </div>
            </main>
        @endif

        {{-- Kompetencer som fragtmanifest --}}
        @if ($skills)
            <footer class="bg-black border-t-4 border-amber-400/30 px-10 py-5">
                <p class="text-[10px] uppercase tracking-[0.4em] text-amber-400/60">Fragtmanifest — Kompetencer</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-400 border-b-2 border-amber-400/50 pb-0.5">{{ $skill }}</span>
                    @endforeach
                </div>
            </footer>
        @endif
    </div>
</body>
</html>