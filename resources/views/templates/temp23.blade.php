{{-- Newspaper Broadsheet --}}
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
        .news-columns { columns: 2; column-gap: 8mm; column-rule: 1px solid #d4d4d4; }
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-neutral-200 min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-serif text-black px-10 py-8">

        {{-- Masthead --}}
        <header class="text-center border-b-4 border-double border-black pb-3">
            <p class="text-[10px] uppercase tracking-[0.5em]">København · {{ now()->format('j. F Y') }} · 1. udgave</p>
            <h1 class="mt-1 text-5xl font-black tracking-tight uppercase">The Daily CV</h1>
            <div class="mt-1 text-[10px] uppercase tracking-[0.3em]">Pris: 1Stillingsamtale — Ansøgning åben for alle</div>
        </header>

        {{-- Hovedoverskrift = navn --}}
        <div class="mt-4 border-b-2 border-black pb-4">
            <h2 class="text-4xl font-black uppercase tracking-tight leading-none text-center">{{ $user->name }}</h2>
            @if ($user->job_title)
                <p class="mt-2 text-center text-lg italic">{{ $user->job_title }} klar til ny udfordring på arbejdsmarkedet</p>
            @endif
            <div class="mt-3 text-center text-xs">
                {{ collect([$user->phone, $user->email])->filter()->implode(' · ') }}
                @if ($user->address || $user->city) · {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}@endif
                @if ($user->birthdate) · Født {{ $user->birthdate->format('d/m/Y') }}@endif
            </div>
        </div>

        {{-- Spalter --}}
        <div class="mt-5 news-columns text-sm">
            <p class="first-letter:text-5xl first-letter:font-black first-letter:float-left first-letter:mr-1 first-letter:leading-[0.8]">
                <span class="font-bold uppercase">{{ $user->name }}</span> er en dedikeret fagperson med dokumenteret erfaring inden for {{ strtolower($user->job_title ?? 'feltet') }}. Nedenfor følger den fulde kronik over vedkommendes erhvervserfaring og uddannelse, som vores redaktion har kunnet dokumentere.
            </p>
            @foreach ($jobs as $job)
                <div class="mt-4 break-inside-avoid">
                    <p class="text-[10px] uppercase tracking-widest text-neutral-500 border-b border-neutral-300 pb-0.5">
                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'i dag' }}
                    </p>
                    <h3 class="mt-1 font-black uppercase leading-tight">{{ $job['title'] }}</h3>
                    <p class="text-xs italic text-neutral-600">{{ $job['company'] }}</p>
                    @if (!empty($job['description']))
                        <p class="mt-1 text-xs leading-relaxed text-neutral-800">{{ $job['description'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Personale-annoncer: kompetencer --}}
        @if ($skills)
            <div class="mt-6 border-4 border-double border-black p-4 text-center">
                <p class="text-xs font-black uppercase tracking-[0.4em]">❦ Kundgjorte kompetencer ❦</p>
                <p class="mt-2 text-xs leading-6 text-neutral-800">
                    @foreach ($skills as $i => $skill)
                        {{ strtoupper($skill) }}{{ $i < count($skills) - 1 ? ' · ' : '' }}
                    @endforeach
                </p>
                <p class="mt-1 text-[10px] italic text-neutral-500">„Ring i dag — kompetencerne er til salg til den rette arbejdsgiver."</p>
            </div>
        @endif
    </div>
</body>
</html>