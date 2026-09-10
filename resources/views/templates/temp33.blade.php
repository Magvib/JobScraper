{{-- Kanban Board --}}
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
    $activeJobs = collect($jobs)->filter(fn ($j) => empty($j['endDate']))->values();
    $doneJobs = collect($jobs)->filter(fn ($j) => !empty($j['endDate']))->values();
@endphp
<body class="bg-slate-200 min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-blue-100 shadow-lg min-h-[297mm] font-sans text-slate-800 px-10 py-9 flex flex-col">

        {{-- Board-header --}}
        <header class="bg-white rounded-lg shadow px-6 py-4 flex items-center gap-5">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-14 h-14 rounded-full object-cover border-2 border-blue-200 shrink-0">
            @endif
            <div class="flex-1">
                <h1 class="text-2xl font-bold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="text-sm text-slate-500">{{ $user->job_title }}</p>
                @endif
            </div>
            <div class="text-right text-xs text-slate-500 leading-5">
                <p>{{ $user->phone }}</p>
                <p class="break-all">{{ $user->email }}</p>
                @if ($user->address || $user->city)
                    <p>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                @endif
                @if ($user->birthdate)
                    <p>Født {{ $user->birthdate->format('d/m/Y') }}</p>
                @endif
            </div>
        </header>

        {{-- Kolonner --}}
        <div class="mt-6 grid grid-cols-3 gap-4 flex-1">

            {{-- DOING --}}
            <div class="bg-slate-100 rounded-lg p-3 flex flex-col">
                <div class="flex items-center justify-between px-1 pb-2">
                    <p class="text-[10px] font-black uppercase tracking-widest text-sky-600">⏳ Doing</p>
                    <span class="bg-sky-600 text-white text-[10px] font-bold rounded-full px-2 py-0.5">{{ count($activeJobs) }}</span>
                </div>
                <div class="space-y-3">
                    @foreach ($activeJobs as $job)
                        <div class="bg-white rounded shadow px-3 py-3 border-t-4 border-t-sky-500">
                            <p class="text-[9px] font-bold text-slate-400 uppercase">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} →</p>
                            <h3 class="mt-1 text-sm font-bold leading-snug">{{ $job['title'] }}</h3>
                            <p class="text-xs text-slate-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-[11px] leading-4 text-slate-600">{{ $job['description'] }}</p>
                            @endif
                            <span class="mt-2 inline-block text-[9px] font-bold uppercase tracking-wider text-sky-600 bg-sky-50 px-2 py-0.5 rounded">AKTIV</span>
                        </div>
                    @endforeach
                    @if (count($activeJobs) === 0)
                        <div class="border-2 border-dashed border-slate-300 rounded px-3 py-6 text-center text-[10px] text-slate-400 uppercase tracking-widest">Tom</div>
                    @endif
                </div>
            </div>

            {{-- DONE --}}
            <div class="bg-slate-100 rounded-lg p-3 flex flex-col">
                <div class="flex items-center justify-between px-1 pb-2">
                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600">✓ Done</p>
                    <span class="bg-emerald-600 text-white text-[10px] font-bold rounded-full px-2 py-0.5">{{ count($doneJobs) }}</span>
                </div>
                <div class="space-y-3">
                    @foreach ($doneJobs as $job)
                        <div class="bg-white rounded shadow px-3 py-3 border-t-4 border-t-emerald-500">
                            <p class="text-[9px] font-bold text-slate-400 uppercase">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} → {{ \Carbon\Carbon::parse($job['endDate'])->format('m/Y') }}</p>
                            <h3 class="mt-1 text-sm font-bold leading-snug">{{ $job['title'] }}</h3>
                            <p class="text-xs text-slate-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-[11px] leading-4 text-slate-600">{{ $job['description'] }}</p>
                            @endif
                            <span class="mt-2 inline-block text-[9px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">LEVERET</span>
                        </div>
                    @endforeach
                    @if (count($doneJobs) === 0)
                        <div class="border-2 border-dashed border-slate-300 rounded px-3 py-6 text-center text-[10px] text-slate-400 uppercase tracking-widest">Tom</div>
                    @endif
                </div>
            </div>

            {{-- BACKLOG: kompetencer --}}
            @if ($skills)
                <div class="bg-slate-100 rounded-lg p-3 flex flex-col">
                    <div class="flex items-center justify-between px-1 pb-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-violet-600">◆ Skills</p>
                        <span class="bg-violet-600 text-white text-[10px] font-bold rounded-full px-2 py-0.5">{{ count($skills) }}</span>
                    </div>
                    <div class="space-y-2.5">
                        @foreach ($skills as $skill)
                            <div class="bg-white rounded shadow px-3 py-2 flex items-center gap-2 border-l-4 border-l-violet-500">
                                <span class="text-violet-400 text-xs">◆</span>
                                <p class="text-xs font-semibold text-slate-700 leading-4">{{ $skill }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <footer class="mt-5 flex justify-between text-[10px] uppercase tracking-widest text-slate-400">
            <span>{{ $user->name }} · Kanban CV</span>
            <span>{{ now()->format('d.m.Y') }}</span>
        </footer>
    </div>
</body>
</html>