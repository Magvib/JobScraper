{{-- Sky Timeline --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-sky-50 shadow-lg overflow-hidden min-h-[297mm] font-sans text-gray-900 flex flex-col">

        {{-- Centreret sky-header --}}
        <header class="bg-sky-600 text-white px-12 pt-12 pb-10 text-center">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 mx-auto rounded-full object-cover ring-4 ring-white/40 mb-4">
            @endif
            <h1 class="text-4xl font-bold tracking-tight">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-1.5 text-lg font-light text-sky-100">{{ $user->job_title }}</p>
            @endif
            <div class="mt-5 flex flex-wrap justify-center gap-x-5 gap-y-1 text-sm text-sky-50">
                @if ($user->phone)
                    <span>{{ $user->phone }}</span>
                    <span class="text-sky-300">|</span>
                @endif
                <span class="break-all">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span class="text-sky-300">|</span>
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span class="text-sky-300">|</span>
                    <span>{{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>
        </header>

        {{-- Kompetencer --}}
        @if ($skills)
            <section class="bg-white px-12 py-5 border-b border-sky-200">
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-sky-600 mr-2">Kompetencer</span>
                    @foreach ($skills as $skill)
                        <span class="text-xs font-semibold text-sky-700 bg-sky-100 border border-sky-200 px-3 py-1 rounded-full">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Centreret tidslinje --}}
        <main class="flex-1 px-12 py-10">
            @if ($jobs)
                <h2 class="text-center text-xs font-bold uppercase tracking-[0.35em] text-sky-700">Erhvervserfaring &amp; Uddannelse</h2>
                <div class="relative mt-8">
                    {{-- Lodret linje --}}
                    <span class="absolute left-1/2 -translate-x-1/2 top-0 bottom-0 w-0.5 bg-sky-300"></span>
                    <div class="space-y-8">
                        @foreach ($jobs as $i => $job)
                            <div class="grid grid-cols-[1fr_1fr] gap-0 items-start">
                                @if ($i % 2 === 0)
                                    <div class="pr-10 text-right">
                                        <p class="text-xs font-bold text-sky-600 uppercase tracking-wide">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                            –
                                            {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                        <h3 class="mt-1 font-bold leading-snug">{{ $job['title'] }}</h3>
                                        <p class="text-sm text-slate-500">{{ $job['company'] }}</p>
                                        @if (!empty($job['description']))
                                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                        @endif
                                    </div>
                                    <div class="relative h-0">
                                        <span class="absolute left-0 -translate-x-1/2 top-1 w-4 h-4 rounded-full bg-white border-2 border-sky-500"></span>
                                    </div>
                                @else
                                    <div class="relative h-0">
                                        <span class="absolute right-0 translate-x-1/2 top-1 w-4 h-4 rounded-full bg-white border-2 border-sky-500"></span>
                                    </div>
                                    <div class="pl-10">
                                        <p class="text-xs font-bold text-sky-600 uppercase tracking-wide">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                            –
                                            {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                        <h3 class="mt-1 font-bold leading-snug">{{ $job['title'] }}</h3>
                                        <p class="text-sm text-slate-500">{{ $job['company'] }}</p>
                                        @if (!empty($job['description']))
                                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </main>
    </div>
</body>
</html>