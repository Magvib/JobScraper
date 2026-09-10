{{-- Boarding Pass --}}
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
        .perforation {
            border-left: 3px dashed #cbd5e1;
            background-image: radial-gradient(circle at 1px 2px, #f8fafc 2px, transparent 3px);
            background-size: 8px 8px;
            background-repeat: repeat-y;
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
    <div class="cv-page max-w-[210mm] mx-auto bg-slate-50 shadow-lg min-h-[297mm] font-mono text-slate-800 px-10 py-10 flex flex-col">

        {{-- Billet-header --}}
        <div class="bg-white border-2 border-slate-300 flex flex-col flex-1 shadow-sm">
            {{-- Hoveddel --}}
            <div class="grid grid-cols-[1fr_62mm]">
                {{-- Venstre: indhold --}}
                <div class="px-8 py-8">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-[0.3em] text-slate-400">Kandidatpas</p>
                        <p class="text-xs font-bold text-slate-400">KLASSE: {{ strtoupper($user->job_title ?? 'PROF') }}</p>
                    </div>
                    <h1 class="mt-4 text-3xl font-bold uppercase tracking-widest">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1 text-sm text-slate-500">{{ $user->job_title }}</p>
                    @endif

                    {{-- Kontakt som billetfelter --}}
                    <div class="mt-8 grid grid-cols-2 gap-x-8 gap-y-4 text-sm">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400">Telefon</p>
                            <p class="mt-0.5 font-semibold">{{ $user->phone ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400">E-mail</p>
                            <p class="mt-0.5 font-semibold break-all">{{ $user->email }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400">Adresse</p>
                            <p class="mt-0.5 font-semibold">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400">Fødselsdato</p>
                            <p class="mt-0.5 font-semibold">{{ $user->birthdate ? $user->birthdate->format('d/m/Y') : '—' }}</p>
                        </div>
                    </div>

                    {{-- Erhvervserfaring --}}
                    @if ($jobs)
                        <div class="mt-8">
                            <p class="text-[10px] uppercase tracking-[0.3em] text-slate-400 border-b border-slate-200 pb-2">Rejselog — Erhvervserfaring &amp; Uddannelse</p>
                            <div class="mt-4 space-y-4">
                                @foreach ($jobs as $job)
                                    <div class="flex gap-4 text-sm">
                                        <p class="text-xs font-bold text-slate-400 whitespace-nowrap pt-0.5">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                            →
                                            {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                        <div>
                                            <p class="font-bold">{{ $job['title'] }}</p>
                                            <p class="text-xs text-slate-500">{{ $job['company'] }}</p>
                                            @if (!empty($job['description']))
                                                <p class="mt-1 text-xs leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Højre: perforeret stump --}}
                <div class="perforation border-y-2 border-slate-300 bg-slate-50 px-6 py-8 flex flex-col items-center text-center">
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}"
                             class="w-24 h-32 object-cover border-2 border-slate-300">
                    @else
                        <div class="w-24 h-32 border-2 border-dashed border-slate-300 flex items-center justify-center text-[10px] text-slate-400 uppercase tracking-widest">Foto</div>
                    @endif

                    {{-- Stempel --}}
                    <div class="mt-6 rotate-[-8deg] border-4 border-emerald-500/70 text-emerald-600 rounded-full px-5 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-[0.25em]">Godkendt</p>
                        <p class="text-[9px] uppercase tracking-widest">{{ now()->format('d.m.Y') }}</p>
                    </div>

                    {{-- Kompetencer --}}
                    @if ($skills)
                        <div class="mt-8 text-left w-full">
                            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 border-b border-slate-200 pb-2">Kompetencer</p>
                            <ul class="mt-3 space-y-1.5 text-xs font-medium text-slate-700">
                                @foreach ($skills as $skill)
                                    <li class="truncate">✓ {{ $skill }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Stregkode --}}
            <div class="border-t-2 border-dashed border-slate-300 px-8 py-4 flex items-end justify-between">
                <div class="flex items-end gap-[3px] h-8">
                    @for ($i = 0; $i < 40; $i++)
                        <span class="{{ $i % 3 === 0 ? 'w-[3px]' : 'w-[1.5px]' }} {{ $i % 4 === 0 ? 'h-8' : 'h-6' }} bg-slate-800 inline-block"></span>
                    @endfor
                </div>
                <p class="text-[9px] tracking-[0.4em] text-slate-400 uppercase">CV {{ strtoupper(preg_replace('/[^a-z0-9]/i', '', $user->name ?? '')) }}</p>
            </div>
        </div>
    </div>
</body>
</html>