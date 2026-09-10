{{-- Comic Book Pop --}}
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
        .halftone {
            background-image: radial-gradient(rgba(0,0,0,0.12) 1px, transparent 1.5px);
            background-size: 8px 8px;
        }
        .comic {
            font-family: 'Comic Sans MS', 'Chalkboard SE', cursive;
        }
        .pow-burst {
            background: #facc15;
            border: 4px solid #000;
            padding: 10px 26px;
            transform: rotate(-3deg);
            box-shadow: 6px 6px 0 #000;
        }
        .speech {
            position: relative;
            background: #fff;
            border: 3px solid #000;
            border-radius: 18px;
            padding: 12px 18px;
        }
        .speech::after {
            content: '';
            position: absolute;
            bottom: -14px; left: 34px;
            border: 14px solid transparent;
            border-top-color: #000;
            border-bottom: 0;
        }
    </style>
</head>
@php
    $user = auth()->user();
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->image ? '/storage/' . $user->image : $user->avatar;
@endphp
<body class="bg-neutral-200 min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg overflow-hidden min-h-[297mm] comic text-black px-12 py-12 relative">

        {{-- Halvtone-baggrund øverst --}}
        <span class="absolute top-0 right-0 w-[90mm] h-[80mm] halftone"></span>

        {{-- Titel: POW! --}}
        <header class="relative">
            <div class="inline-block pow-burst">
                <h1 class="text-4xl font-black uppercase tracking-tight">{{ $user->name }}!</h1>
            </div>
            @if ($user->job_title)
                <p class="mt-4 text-xl font-bold uppercase text-red-600 tracking-wide">{{ $user->job_title }}</p>
            @endif
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="mt-5 w-24 h-24 object-cover border-4 border-black shadow-[6px_6px_0_#000] -rotate-2">
            @endif
        </header>

        {{-- Taleboble: kontakt --}}
        <div class="mt-8 speech max-w-[130mm] text-sm leading-6">
            <p><strong>Helt:</strong> {{ $user->phone ?? '—' }} · {{ $user->email }}</p>
            @if ($user->address || $user->city)
                <p><strong>Hemmelig base:</strong> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
            @endif
            @if ($user->birthdate)
                <p><strong>Skabt:</strong> {{ $user->birthdate->format('d/m/Y') }}</p>
            @endif
        </div>

        {{-- Erhvervserfaring som tegneserieruder --}}
        @if ($jobs)
            <h2 class="mt-10 text-2xl font-black uppercase tracking-wide border-b-4 border-black pb-1">Kapitel 1: Erhvervserfaring!</h2>
            <div class="mt-5 space-y-5">
                @foreach ($jobs as $i => $job)
                    <article class="border-4 border-black px-5 py-4 {{ $i % 2 === 0 ? '-rotate-[0.5deg] bg-yellow-50' : 'rotate-[0.5deg] bg-blue-50' }} shadow-[4px_4px_0_#000]">
                        <p class="text-xs font-black uppercase tracking-widest text-red-600">
                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                            –
                            {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'FORTSÆTTER…' }}
                        </p>
                        <h3 class="mt-1 text-lg font-black uppercase leading-tight">{{ $job['title'] }}</h3>
                        <p class="text-sm font-bold text-blue-700">hos {{ $job['company'] }}</p>
                        @if (!empty($job['description']))
                            <p class="mt-2 text-sm leading-relaxed">{{ $job['description'] }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

        {{-- Kompetencer som superkræfter --}}
        @if ($skills)
            <h2 class="mt-10 text-2xl font-black uppercase tracking-wide border-b-4 border-black pb-1">Superkræfter!</h2>
            <div class="mt-5 flex flex-wrap gap-3">
                @foreach ($skills as $i => $skill)
                    <span class="text-sm font-black uppercase {{ ['bg-red-500 text-white rotate-[-2deg]', 'bg-yellow-300 text-black rotate-[2deg]', 'bg-blue-500 text-white rotate-[-1deg]', 'bg-green-400 text-black rotate-[3deg]'][$i % 4] }} border-2 border-black px-4 py-2 shadow-[3px_3px_0_#000]">
                        {{ $skill }}
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Slutboble --}}
        <p class="mt-10 text-right text-3xl font-black uppercase text-red-600 rotate-[-3deg]">Slut!</p>
    </div>
</body>
</html>