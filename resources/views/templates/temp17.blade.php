{{-- Magazine Cover --}}
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
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg overflow-hidden min-h-[297mm] font-sans text-gray-900 flex flex-col">

        {{-- Full-bleed foto-hero --}}
        <header class="relative h-[95mm]">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="absolute inset-0 w-full h-full object-cover">
            @else
                <div class="absolute inset-0 bg-zinc-800"></div>
            @endif
            <div class="absolute inset-0 bg-linear-to-t from-black/85 via-black/40 to-black/10"></div>
            <div class="absolute inset-x-0 bottom-0 px-10 pb-8 text-white">
                <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-red-400">Curriculum Vitae</p>
                <h1 class="mt-2 text-6xl font-black tracking-tighter leading-none drop-shadow-lg">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg font-light text-white/90">{{ $user->job_title }}</p>
                @endif
            </div>
            <span class="absolute top-6 right-8 text-xs font-bold uppercase tracking-[0.3em] text-white/90 border border-white/50 px-3 py-1.5">2026</span>
        </header>

        {{-- Kontakt-strip --}}
        <div class="bg-red-600 text-white px-10 py-3 flex flex-wrap gap-x-6 gap-y-1 text-xs font-semibold">
            @if ($user->phone)
                <span>{{ $user->phone }}</span>
            @endif
            <span class="break-all">{{ $user->email }}</span>
            @if ($user->address || $user->city)
                <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)
                <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </div>

        {{-- To-spaltet magasinindhold --}}
        <div class="flex-1 px-10 py-10 grid grid-cols-[1fr_54mm] gap-10">
            <main>
                <h2 class="text-2xl font-black tracking-tight flex items-center gap-3">
                    <span class="w-2 h-6 bg-red-600 inline-block"></span>
                    Erhvervserfaring
                </h2>
                <div class="mt-5 space-y-6">
                    @foreach ($jobs as $job)
                        <article>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-red-600">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                –
                                {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-1 text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
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
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-900 border-b-2 border-red-600 pb-2">Kompetencer</h2>
                    <ul class="mt-5 space-y-2.5 text-sm font-medium">
                        @foreach ($skills as $skill)
                            <li class="flex items-center gap-2 text-slate-700">
                                <span class="w-1.5 h-1.5 bg-red-600 rotate-45 shrink-0"></span>
                                {{ $skill }}
                            </li>
                        @endforeach
                    </ul>
                </aside>
            @endif
        </div>

        {{-- Magasin-footer --}}
        <footer class="border-t border-slate-200 px-10 py-4 flex justify-between text-[10px] uppercase tracking-[0.3em] text-slate-400">
            <span>{{ $user->name }}</span>
            <span>CV — 2026</span>
        </footer>
    </div>
</body>
</html>