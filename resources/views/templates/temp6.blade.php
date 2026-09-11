{{-- Swiss Editorial --}}
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
<body class="bg-white min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-gray-900 px-14 py-12 flex flex-col">

        {{-- Redaktionel header – skæv asymmetri --}}
        <header class="grid grid-cols-[1fr_auto] items-start gap-8">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-emerald-600">Curriculum Vitae</p>
                <h1 class="mt-3 text-6xl font-extrabold tracking-tighter leading-[0.95]">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-3 text-xl text-stone-500">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover grayscale contrast-125 shrink-0 rounded-none">
            @endif
        </header>
        <div class="mt-8 h-1.5 bg-emerald-600"></div>

        {{-- Kontakt som en linje --}}
        <p class="mt-5 text-xs text-stone-500 leading-6">
            @if ($user->phone)<span class="font-bold text-gray-900 uppercase tracking-wider">Telefon</span> {{ $user->phone }} &nbsp;·&nbsp; @endif
            <span class="font-bold text-gray-900 uppercase tracking-wider">E-mail</span> {{ $user->email }}@if ($user->address || $user->city) &nbsp;·&nbsp;
            <span class="font-bold text-gray-900 uppercase tracking-wider">Adresse</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}@endif
            @if ($user->birthdate) &nbsp;·&nbsp; <span class="font-bold text-gray-900 uppercase tracking-wider">Født</span> {{ $user->birthdate->format('d/m/Y') }}@endif
        </p>

        <div class="mt-10 grid flex-1 grid-cols-[1fr_55mm] gap-12">
            {{-- Erhvervserfaring --}}
            <main>
                <h2 class="text-[10px] font-bold uppercase tracking-[0.4em] text-emerald-600 border-b border-stone-300 pb-2">Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-6 space-y-7">
                    @foreach ($jobs as $job)
                        <article>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-stone-400">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                –
                                {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-1 text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                            <p class="text-sm text-emerald-700 font-medium">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </main>

            {{-- Kompetencer --}}
            @if ($skills)
                <aside>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.4em] text-emerald-600 border-b border-stone-300 pb-2">Kompetencer</h2>
                    <ul class="mt-6 space-y-2 text-sm font-medium">
                        @foreach ($skills as $skill)
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-emerald-600 shrink-0"></span>
                                {{ $skill }}
                            </li>
                        @endforeach
                    </ul>
                </aside>
            @endif
        </div>

        {{-- Footer --}}
        <footer class="mt-10 border-t border-stone-200 pt-4 text-[10px] uppercase tracking-[0.3em] text-stone-400 flex justify-between">
            <span>{{ $user->name }}</span>
            <span>CV</span>
        </footer>
    </div>
</body>
</html>