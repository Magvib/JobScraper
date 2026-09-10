{{-- Bold Amber --}}
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
@endphp
<body class="bg-white min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg overflow-hidden min-h-[297mm] font-sans text-gray-900 flex flex-col">

        {{-- Stor typografisk header --}}
        <header class="bg-stone-900 text-white px-12 py-14 flex items-end justify-between gap-8">
            <div>
                @if ($user->job_title)
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-400">{{ $user->job_title }}</p>
                @endif
                <h1 class="mt-3 text-5xl font-black uppercase leading-none tracking-tight">{{ $user->name }}</h1>
                <div class="mt-6 flex flex-wrap gap-x-6 gap-y-1 text-sm text-stone-300">
                    @if ($user->phone)
                        <span>{{ $user->phone }}</span>
                    @endif
                    <span class="break-all">{{ $user->email }}</span>
                    @if ($user->address || $user->city)
                        <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                    @endif
                    @if ($user->birthdate)
                        <span>{{ $user->birthdate->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 rounded-2xl object-cover ring-4 ring-amber-400/80 shrink-0 rotate-3">
            @endif
        </header>

        {{-- Kompetencer som gult bånd --}}
        @if ($skills)
            <div class="bg-amber-400 px-12 py-4 flex flex-wrap items-center gap-x-3 gap-y-1">
                <span class="text-xs font-black uppercase tracking-[0.2em] text-stone-900">Kompetencer</span>
                @foreach ($skills as $skill)
                    <span class="text-xs font-bold bg-stone-900 text-amber-400 px-3 py-1 rounded-full">{{ $skill }}</span>
                @endforeach
            </div>
        @endif

        {{-- Erhvervserfaring & uddannelse som kort --}}
        <main class="flex-1 px-12 py-12">
            @if ($jobs)
                <h2 class="text-2xl font-black uppercase tracking-tight">Erfaring &amp; Uddannelse</h2>
                <div class="mt-6 grid gap-5">
                    @foreach ($jobs as $job)
                        <article class="bg-stone-100 rounded-xl px-6 py-5 border-l-8 border-amber-400">
                            <div class="flex items-baseline justify-between gap-4">
                                <h3 class="font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-xs font-bold uppercase tracking-wide text-stone-500 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="mt-0.5 text-sm font-semibold text-amber-600">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-sm leading-relaxed text-stone-700">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </main>
    </div>
</body>
</html>