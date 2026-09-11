{{-- Chalkboard Menu --}}
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
        .chalk-frame {
            border: 14px solid #6b4a2f;
            box-shadow: inset 0 0 40px rgba(0,0,0,0.5);
            background: #2f3b32;
        }
        .chalk { font-family: 'Bradley Hand', 'Comic Sans MS', 'Segoe Print', cursive; }
        .chalk-line { border-bottom: 2px dashed rgba(255,255,255,0.35); }
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-[#8a7156] min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto shadow-lg min-h-[297mm] px-10 py-10">
        <div class="chalk-frame chalk min-h-[265mm] text-[#e8e4d8] px-10 py-10 relative">

            {{-- Vasketavle-titel --}}
            <header class="text-center">
                <p class="text-sm uppercase tracking-[0.5em] text-[#e8e4d8]/60">I dagens</p>
                <h1 class="mt-2 text-5xl font-bold tracking-wide">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-2xl text-[#fbbf24] italic">{{ $user->job_title }}</p>
                @endif
                <div class="mt-4 flex items-center justify-center gap-3">
                    <span class="h-0.5 w-20 bg-[#e8e4d8]/40"></span>
                    <span>✦</span>
                    <span class="h-0.5 w-20 bg-[#e8e4d8]/40"></span>
                </div>
            </header>

            {{-- Kontakt på tavlen --}}
            <div class="mt-6 text-center text-sm leading-6 text-[#e8e4d8]/80">
                <p>{{ $user->phone ?? '' }}@if ($user->phone && $user->email) · @endif{{ $user->email }}</p>
                @if ($user->address || $user->city)
                    <p>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                @endif
                @if ($user->birthdate)
                    <p>Født {{ $user->birthdate->format('d/m/Y') }}</p>
                @endif
            </div>

            {{-- Foto i krittet --}}
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="mt-6 mx-auto w-24 h-24 rounded-full object-cover border-4 border-dashed border-[#e8e4d8]/50 rotate-2">
            @endif

            {{-- Menusektion: erfaring --}}
            @if ($jobs)
                <section class="mt-10">
                    <h2 class="text-2xl font-bold text-center uppercase tracking-widest text-[#fbbf24]">Vores Erfaringer</h2>
                    <div class="mt-6 space-y-5">
                        @foreach ($jobs as $job)
                            <div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <h3 class="text-lg font-bold">{{ $job['title'] }}</h3>
                                    <p class="text-sm text-[#e8e4d8]/70 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <div class="chalk-line"></div>
                                <p class="mt-1.5 text-sm italic text-[#e8e4d8]/75">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-1 text-sm leading-relaxed text-[#e8e4d8]/85">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Dagens specialer: kompetencer --}}
            @if ($skills)
                <section class="mt-10 text-center">
                    <h2 class="text-2xl font-bold uppercase tracking-widest text-[#fbbf24]">Dagens Kompetencer</h2>
                    <p class="mt-4 text-lg leading-8">
                        {{ collect($skills)->implode(' · ') }}
                    </p>
                    <p class="mt-2 text-sm italic text-[#e8e4d8]/60">— serveres med engagement —</p>
                </section>
            @endif

            {{-- Kridtholder nederst --}}
            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2">
                <span class="w-10 h-1.5 bg-[#e8e4d8]/70 rounded-full"></span>
                <span class="w-10 h-1.5 bg-[#fbbf24]/80 rounded-full"></span>
                <span class="w-10 h-1.5 bg-[#e8e4d8]/40 rounded-full"></span>
            </div>
        </div>
    </div>
</body>
</html>