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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-serif text-gray-900 px-16 py-14">

        {{-- Centreret klassisk header --}}
        <header class="text-center border-b-2 border-stone-800 pb-6">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 mx-auto rounded-full object-cover border-2 border-stone-300 mb-4 grayscale">
            @endif
            <h1 class="text-4xl font-bold tracking-wider uppercase">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 text-base italic text-stone-600 tracking-wide">{{ $user->job_title }}</p>
            @endif
            <div class="mt-4 flex flex-wrap justify-center gap-x-4 text-sm text-stone-600">
                @if ($user->phone)
                    <span>{{ $user->phone }}</span>
                    <span class="text-stone-400">·</span>
                @endif
                <span class="break-all">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span class="text-stone-400">·</span>
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span class="text-stone-400">·</span>
                    <span>{{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>
        </header>

        {{-- Erhvervserfaring & uddannelse --}}
        @if ($jobs)
            <section class="mt-8">
                <h2 class="text-sm font-bold uppercase tracking-[0.35em] text-center text-stone-800">Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-2 mx-auto w-24 border-t border-stone-400"></div>
                <div class="mt-6 space-y-7">
                    @foreach ($jobs as $job)
                        <article class="grid grid-cols-[30mm_1fr] gap-4">
                            <p class="text-xs text-stone-500 leading-5 uppercase tracking-wide pt-1">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>
                                – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <div>
                                <h3 class="font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm italic text-stone-600">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-stone-700">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Kompetencer --}}
        @if ($skills)
            <section class="mt-10 border-t border-stone-300 pt-6">
                <h2 class="text-sm font-bold uppercase tracking-[0.35em] text-center text-stone-800">Kompetencer</h2>
                <div class="mt-2 mx-auto w-24 border-t border-stone-400"></div>
                <p class="mt-4 text-center text-sm leading-7 text-stone-700">
                    {{ collect($skills)->implode('  •  ') }}
                </p>
            </section>
        @endif
    </div>
</body>
</html>