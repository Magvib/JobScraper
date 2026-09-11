{{-- Map Journey --}}
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
        .route-bg {
            background-image: radial-gradient(rgba(15, 118, 110, 0.08) 1.5px, transparent 2px);
            background-size: 22px 22px;
        }
        .compass {
            width: 0; height: 0;
            border-left: 5mm solid transparent;
            border-right: 5mm solid transparent;
            border-bottom: 14mm solid #f59e0b;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen py-8 px-4">
    <div class="cv-page route-bg max-w-[210mm] mx-auto bg-emerald-50 shadow-lg min-h-[297mm] font-sans text-slate-800 px-12 py-12 relative overflow-hidden">

        {{-- Kompas i hjørnet --}}
        <span class="compass absolute top-10 right-10 opacity-20 inline-block"></span>

        {{-- Rejseheader --}}
        <header class="flex items-center gap-7">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 rounded-full object-cover border-4 border-emerald-600 shrink-0">
            @endif
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-emerald-600">Ekspedition: Karrieren</p>
                <h1 class="mt-1.5 text-4xl font-bold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1 text-emerald-700 font-medium">{{ $user->job_title }}</p>
                @endif
                <div class="mt-2.5 flex flex-wrap gap-x-4 gap-y-0.5 text-xs text-slate-500">
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
            </div>
        </header>

        <div class="mt-10 grid grid-cols-[1fr_50mm] gap-12">
            {{-- Rutekort: erfaring --}}
            <main>
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-emerald-700 border-b-2 border-emerald-600 pb-2">Rutebeskrivelse — Erhvervserfaring &amp; Uddannelse</h2>
                <div class="relative mt-6 pl-2">
                    {{-- stiplet rute --}}
                    <span class="absolute left-4.25 top-2 bottom-2 border-l-2 border-dashed border-emerald-400"></span>
                    <div class="space-y-7">
                        @foreach ($jobs as $i => $job)
                            <div class="relative pl-8">
                                {{-- pushpin --}}
                                <span class="absolute left-0 top-0.5 w-5 h-5 rounded-full bg-emerald-600 border-2 border-white shadow flex items-center justify-center text-[9px] font-bold text-white">{{ $i + 1 }}</span>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-600">
                                    Stop {{ $i + 1 }} ·
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'undervejs' }}
                                </p>
                                <h3 class="mt-1 text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm text-slate-500">📍 {{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <p class="mt-8 text-[10px] italic text-slate-400">
                    ^~^~^~^~^~^~^~^~^~^~^~^~^~^~^~^~^~^~^~^~^~^~^~^~^~^~^~^
                </p>
            </main>

            {{-- Proviantliste: kompetencer --}}
            @if ($skills)
                <aside>
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-emerald-700 border-b-2 border-emerald-600 pb-2">Proviant — Kompetencer</h2>
                    <ul class="mt-6 space-y-3 text-sm">
                        @foreach ($skills as $skill)
                            <li class="bg-white rounded shadow-sm px-3.5 py-2.5 border border-emerald-100 font-medium flex items-center gap-2">
                                <span class="text-emerald-500">⚑</span>
                                {{ $skill }}
                            </li>
                        @endforeach
                    </ul>
                </aside>
            @endif
        </div>

        <footer class="mt-12 flex justify-between text-[10px] uppercase tracking-[0.25em] text-emerald-600/70 border-t border-dashed border-emerald-300 pt-4">
            <span>{{ $user->name }}</span>
            <span>Kort målt 1:1 · {{ now()->format('Y') }}</span>
        </footer>
    </div>
</body>
</html>