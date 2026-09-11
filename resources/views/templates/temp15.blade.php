{{-- Infographic Bars --}}
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
    $barWidths = [88, 76, 94, 68, 82, 72];
@endphp
<body class="bg-white min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-slate-50 shadow-lg min-h-[297mm] font-sans text-gray-900 px-12 py-12 flex flex-col">

        {{-- Kompakt header med statistik-felt --}}
        <header class="flex items-center justify-between gap-8">
            <div>
                @if ($user->job_title)
                    <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-cyan-600">{{ $user->job_title }}</p>
                @endif
                <h1 class="mt-2 text-4xl font-bold tracking-tight">{{ $user->name }}</h1>
                <div class="mt-4 grid grid-cols-2 gap-x-6 gap-y-2 text-xs text-slate-500">
                    @if ($user->phone)
                        <p><span class="font-bold text-slate-800">Tlf</span> {{ $user->phone }}</p>
                    @endif
                    <p class="break-all"><span class="font-bold text-slate-800">Mail</span> {{ $user->email }}</p>
                    @if ($user->address || $user->city)
                        <p class="col-span-2"><span class="font-bold text-slate-800">Adresse</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                    @endif
                    @if ($user->birthdate)
                        <p><span class="font-bold text-slate-800">Født</span> {{ $user->birthdate->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 rounded-2xl object-cover ring-1 ring-slate-200 shrink-0">
            @endif
        </header>

        <div class="mt-10 grid grid-cols-[54mm_1fr] gap-10 flex-1">
            {{-- Kompetencer som bjælker --}}
            @if ($skills)
                <aside>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-cyan-600 border-b border-slate-300 pb-2">Kompetencer</h2>
                    <div class="mt-5 space-y-4">
                        @foreach ($skills as $i => $skill)
                            <div>
                                <div class="flex justify-between text-xs font-semibold text-slate-700">
                                    <span>{{ $skill }}</span>
                                </div>
                                <div class="mt-1.5 h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-linear-to-r from-cyan-500 to-teal-400"
                                         style="width: {{ $barWidths[$i % count($barWidths)] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </aside>
            @endif

            {{-- Erhvervserfaring i gitter --}}
            <main>
                <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-cyan-600 border-b border-slate-300 pb-2">Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-5 space-y-5">
                    @foreach ($jobs as $job)
                        <article class="bg-white border border-slate-200 rounded-lg px-5 py-4">
                            <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                <h3 class="font-bold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-[10px] font-bold text-cyan-600 uppercase tracking-wide whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="mt-0.5 text-sm text-slate-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </main>
        </div>
    </div>
</body>
</html>