{{-- Blueprint --}}
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
        .cv-page {
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
            background-color: #0e3a5c;
            background-image:
                linear-gradient(rgba(255,255,255,0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.07) 1px, transparent 1px),
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 40px 40px, 40px 40px, 8px 8px, 8px 8px;
        }
    </style>
</head>
@php
    $user = auth()->user();
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->image ? '/storage/' . $user->image : $user->avatar;
@endphp
<body class="bg-white min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto shadow-lg min-h-[297mm] font-mono text-sky-100 px-12 py-10 relative">

        {{-- Teknisk ramme --}}
        <div class="border-2 border-sky-200/70 px-8 py-9 min-h-[255mm] flex flex-col relative">
            <span class="absolute -top-3 left-8 bg-[#0e3a5c] px-3 text-[10px] font-bold uppercase tracking-[0.4em] text-sky-200">Tegning nr. CV-01</span>
            <span class="absolute -top-3 right-8 bg-[#0e3a5c] px-3 text-[10px] font-bold tracking-widest text-sky-200">SKALA 1:1</span>

            {{-- Header --}}
            <header class="flex items-start justify-between gap-8 border-b border-dashed border-sky-200/50 pb-6">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.35em] text-sky-300/70">Projektkortlægning</p>
                    <h1 class="mt-2 text-4xl font-bold uppercase tracking-widest text-white">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-sm text-sky-300">&lt;{{ $user->job_title }}/&gt;</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-20 h-20 object-cover border-2 border-dashed border-sky-200/60 p-1 shrink-0">
                @endif
            </header>

            {{-- Kontakt med dimensionslinjer --}}
            <div class="mt-5 flex flex-wrap gap-x-8 gap-y-2 text-xs text-sky-200/90">
                @if ($user->phone)
                    <span>[ TLF: {{ $user->phone }} ]</span>
                @endif
                <span class="break-all">[ MAIL: {{ $user->email }} ]</span>
                @if ($user->address || $user->city)
                    <span>[ ADR: {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }} ]</span>
                @endif
                @if ($user->birthdate)
                    <span>[ FØDT: {{ $user->birthdate->format('d/m/Y') }} ]</span>
                @endif
            </div>

            <div class="mt-8 grid grid-cols-[1fr_54mm] gap-10 flex-1">
                {{-- Erhvervserfaring --}}
                <main>
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-sky-300 border-b border-sky-200/50 pb-2">Fig. 1 — Erhvervserfaring &amp; Uddannelse</h2>
                    <div class="mt-5 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="border border-sky-200/40 px-4 py-3">
                                <p class="text-[10px] text-sky-300/80 tracking-widest">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    —
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'NU' }}
                                </p>
                                <h3 class="mt-1 font-bold text-white">{{ $job['title'] }}</h3>
                                <p class="text-xs text-sky-300">↳ {{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-xs leading-relaxed text-sky-100/80">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </main>

                {{-- Kompetencer --}}
                @if ($skills)
                    <aside>
                        <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-sky-300 border-b border-sky-200/50 pb-2">Fig. 2 — Kompetencer</h2>
                        <ul class="mt-5 space-y-2 text-xs">
                            @foreach ($skills as $skill)
                                <li class="flex items-center gap-2 text-sky-100">
                                    <span class="text-sky-300">▸</span>
                                    {{ $skill }}
                                </li>
                            @endforeach
                        </ul>
                    </aside>
                @endif
            </div>

            {{-- Tegningsfod --}}
            <footer class="mt-8 border-t border-sky-200/50 pt-4 grid grid-cols-3 gap-4 text-[10px] uppercase tracking-widest text-sky-300/70">
                <span>Tegnet af: {{ $user->name }}</span>
                <span class="text-center">Dato: {{ now()->format('d.m.Y') }}</span>
                <span class="text-right">Rev. A</span>
            </footer>
        </div>
    </div>
</body>
</html>