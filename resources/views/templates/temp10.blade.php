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
    <div class="cv-page max-w-[210mm] mx-auto bg-linear-to-br from-rose-100 via-pink-50 to-purple-100 shadow-lg min-h-[297mm] font-sans text-gray-900 px-10 py-10 flex flex-col">

        {{-- Flydende hvidt kort --}}
        <div class="bg-white/80 backdrop-blur rounded-3xl shadow-xl border border-white px-10 py-10 flex-1 flex flex-col">

            {{-- Header med monogram --}}
            <header class="flex items-center gap-6">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-20 h-20 rounded-2xl object-cover shadow-md shrink-0">
                @else
                    <div class="w-20 h-20 rounded-2xl bg-rose-200 flex items-center justify-center shrink-0">
                        <span class="text-3xl font-black text-rose-500">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    </div>
                @endif
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight bg-linear-to-r from-rose-500 to-purple-500 bg-clip-text text-transparent">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1 text-base font-medium text-rose-500">{{ $user->job_title }}</p>
                    @endif
                </div>
            </header>

            {{-- Kontakt --}}
            <div class="mt-6 flex flex-wrap gap-2 text-xs font-medium text-gray-600">
                @if ($user->phone)
                    <span class="bg-rose-50 border border-rose-100 rounded-full px-3 py-1.5">{{ $user->phone }}</span>
                @endif
                <span class="bg-rose-50 border border-rose-100 rounded-full px-3 py-1.5 break-all">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span class="bg-rose-50 border border-rose-100 rounded-full px-3 py-1.5">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span class="bg-rose-50 border border-rose-100 rounded-full px-3 py-1.5">{{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>

            <div class="mt-8 grid grid-cols-[1fr_52mm] gap-8 flex-1">
                {{-- Erhvervserfaring --}}
                <main>
                    <h2 class="text-xs font-bold uppercase tracking-[0.25em] text-rose-500">Erhvervserfaring &amp; Uddannelse</h2>
                    <div class="mt-5 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="relative pl-5">
                                <span class="absolute left-0 top-1.5 w-2.5 h-2.5 rounded-md bg-linear-to-br from-rose-400 to-purple-400"></span>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-rose-400">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-bold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm text-slate-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </main>

                {{-- Kompetencer --}}
                @if ($skills)
                    <aside>
                        <h2 class="text-xs font-bold uppercase tracking-[0.25em] text-rose-500">Kompetencer</h2>
                        <div class="mt-5 flex flex-wrap gap-2">
                            @foreach ($skills as $skill)
                                <span class="text-xs font-semibold text-purple-600 bg-purple-50 border border-purple-100 px-3 py-1.5 rounded-full">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </aside>
                @endif
            </div>
        </div>
    </div>
</body>
</html>