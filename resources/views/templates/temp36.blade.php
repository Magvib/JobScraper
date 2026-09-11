{{-- Ghost Watermark --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ $user->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .cv-page { box-shadow: none !important; margin: 0 !important; max-width: 100% !important; }
        }
        .cv-page { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .ghost {
            position: absolute;
            right: -10mm;
            bottom: -18mm;
            font-size: 340px;
            line-height: 1;
            font-weight: 900;
            color: rgba(226, 232, 240, 0.55);
            z-index: 0;
            user-select: none;
        }
        .cv-content { position: relative; z-index: 10; }
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
    $initial = strtoupper(mb_substr(trim($user->name ?? 'C'), 0, 1));
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg overflow-hidden min-h-[297mm] font-sans text-slate-800 relative">

        {{-- Kæmpe spøgelses-bogstav bag indholdet --}}
        <span class="ghost">{{ $initial }}</span>

        <div class="cv-content px-14 py-12 flex flex-col min-h-[297mm]">
            <header class="flex items-center gap-7">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 rounded-full object-cover border border-slate-200 shrink-0">
                @endif
                <div>
                    <h1 class="text-4xl font-bold tracking-tight text-slate-900">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-lg text-slate-400 font-light">{{ $user->job_title }}</p>
                    @endif>
                </div>
            </header>

            <div class="mt-4 border-b border-slate-100 pb-4 flex flex-wrap gap-x-6 gap-y-1 text-xs text-slate-400">
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

            <div class="mt-10 grid grid-cols-[1fr_54mm] gap-12 flex-1">
                {{-- Erhvervserfaring --}}
                <main>
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-400 border-l-4 border-slate-900 pl-3">Erhvervserfaring &amp; Uddannelse</h2>
                    <div class="mt-7 space-y-7">
                        @foreach ($jobs as $job)
                            <article>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-300">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 text-xl font-bold leading-snug text-slate-900">{{ $job['title'] }}</h3>
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
                        <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-400 border-l-4 border-slate-900 pl-3">Kompetencer</h2>
                        <div class="mt-7 flex flex-wrap gap-2">
                            @foreach ($skills as $skill)
                                <span class="text-xs font-medium text-slate-600 border border-slate-200 px-3 py-1.5 rounded-full">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </aside>
                @endif
            </div>

            <footer class="mt-10 pt-5 border-t border-slate-100 text-[10px] uppercase tracking-[0.35em] text-slate-300 flex justify-between">
                <span>{{ $user->name }}</span>
                <span>{{ now()->format('Y') }}</span>
            </footer>
        </div>
    </div>
</body>
</html>