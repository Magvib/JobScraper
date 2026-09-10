{{-- Brutalist --}}
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
        .brutal-shadow { box-shadow: 8px 8px 0 0 #000; }
    </style>
</head>
@php
    $user = auth()->user();
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->image ? '/storage/' . $user->image : $user->avatar;
@endphp
<body class="bg-white min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-black px-10 py-10">

        {{-- Brutalist header med kantede bokse --}}
        <header class="border-4 border-black brutal-shadow px-8 py-8 bg-yellow-300">
            <div class="flex items-center justify-between gap-6">
                <div>
                    <h1 class="text-5xl font-black uppercase leading-[0.95] tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 inline-block bg-black text-yellow-300 text-xs font-bold uppercase tracking-[0.2em] px-3 py-1.5">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 object-cover border-4 border-black shrink-0">
                @endif
            </div>
        </header>

        {{-- Kontakt i fire kantede bokse --}}
        <div class="mt-8 grid grid-cols-2 gap-4">
            @if ($user->phone)
                <div class="border-2 border-black px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em]">Telefon</p>
                    <p class="mt-1 text-sm font-bold">{{ $user->phone }}</p>
                </div>
            @endif
            <div class="border-2 border-black px-4 py-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.25em]">E-mail</p>
                <p class="mt-1 text-sm font-bold break-all">{{ $user->email }}</p>
            </div>
            @if ($user->address || $user->city)
                <div class="border-2 border-black px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em]">Adresse</p>
                    <p class="mt-1 text-sm font-bold">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                </div>
            @endif
            @if ($user->birthdate)
                <div class="border-2 border-black px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em]">Fødselsdato</p>
                    <p class="mt-1 text-sm font-bold">{{ $user->birthdate->format('d/m/Y') }}</p>
                </div>
            @endif
        </div>

        {{-- Kompetencer --}}
        @if ($skills)
            <section class="mt-8">
                <h2 class="inline-block bg-black text-white text-sm font-black uppercase tracking-[0.25em] px-4 py-2">Kompetencer</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-xs font-black uppercase border-2 border-black px-3 py-1.5">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Erhvervserfaring --}}
        @if ($jobs)
            <section class="mt-8">
                <h2 class="inline-block bg-black text-white text-sm font-black uppercase tracking-[0.25em] px-4 py-2">Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-4 space-y-4">
                    @foreach ($jobs as $job)
                        <article class="border-2 border-black px-5 py-4">
                            <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                <h3 class="font-black text-lg uppercase leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-xs font-bold border-b-2 border-black pb-0.5 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="mt-1 text-sm font-bold text-stone-600 uppercase tracking-wide">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-sm leading-relaxed text-stone-700">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</body>
</html>