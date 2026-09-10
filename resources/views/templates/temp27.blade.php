{{-- Law Letterhead --}}
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
    $initials = collect(explode(' ', trim($user->name ?? '')))->map(fn ($w) => strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp
<body class="bg-stone-200 min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#faf7ef] shadow-lg min-h-[297mm] font-serif text-stone-800 px-16 py-14 flex flex-col">

        {{-- Brevhoved med monogram --}}
        <header class="text-center border-b-4 border-double border-stone-700 pb-6">
            <div class="w-20 h-20 mx-auto rounded-full border-2 border-stone-600 flex items-center justify-center bg-white">
                <span class="text-2xl font-bold tracking-[0.2em] text-stone-700">{{ $initials }}</span>
            </div>
            <h1 class="mt-5 text-3xl font-bold uppercase tracking-[0.25em] text-stone-800">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 text-sm italic text-stone-500 tracking-wide">{{ $user->job_title }}</p>
            @endif
        </header>

        {{-- Adgangsinformation --}}
        <div class="mt-6 grid grid-cols-3 gap-6 text-center text-xs uppercase tracking-[0.2em] text-stone-500">
            <div class="border-r border-stone-300">
                <p class="font-bold text-stone-600">Telefon</p>
                <p class="mt-1 normal-case tracking-normal">{{ $user->phone ?? '—' }}</p>
            </div>
            <div class="border-r border-stone-300">
                <p class="font-bold text-stone-600">Korrespondance</p>
                <p class="mt-1 normal-case tracking-normal break-all">{{ $user->email }}</p>
            </div>
            <div>
                <p class="font-bold text-stone-600">Domicil</p>
                <p class="mt-1 normal-case tracking-normal">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') ?: '—' }}</p>
            </div>
        </div>

        {{-- Sagsliste: erhvervserfaring --}}
        @if ($jobs)
            <section class="mt-10 flex-1">
                <h2 class="text-sm font-bold uppercase tracking-[0.35em] text-stone-700 text-center">Sagsliste — Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-6 space-y-7">
                    @foreach ($jobs as $i => $job)
                        <article>
                            <div class="flex items-baseline gap-3">
                                <span class="text-xs text-stone-400 font-bold">§ {{ $i + 1 }}.</span>
                                <span class="flex-1 border-b border-stone-300"></span>
                                <span class="text-xs text-stone-500 tracking-widest">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    —
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'fortsættende' }}
                                </span>
                            </div>
                            <div class="mt-2 pl-8">
                                <h3 class="text-lg font-bold text-stone-800">{{ $job['title'] }}</h3>
                                <p class="text-sm italic text-stone-500">hos {{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Kompetencer --}}
        @if ($skills)
            <section class="mt-10">
                <h2 class="text-sm font-bold uppercase tracking-[0.35em] text-stone-700 text-center">Specialekompetencer</h2>
                <div class="mt-5 mx-auto max-w-[150mm] border-t border-b border-stone-300 py-4 px-6">
                    <p class="text-center text-sm leading-7 text-stone-600">
                        {{ collect($skills)->implode(' · ') }}
                    </p>
                </div>
            </section>
        @endif

        {{-- Fod med dato --}}
        <footer class="mt-auto pt-8 text-center text-xs italic text-stone-400">
            @if ($user->birthdate)Født {{ $user->birthdate->format('d/m/Y') }} · @endif
            Udleveret den {{ now()->locale('da')->translatedFormat('j. F Y') }}
        </footer>
    </div>
</body>
</html>