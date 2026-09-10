{{-- Navy Gold --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-blue-950 text-blue-50 shadow-lg min-h-[297mm] font-serif px-14 py-14 flex flex-col">

        {{-- Luksuriøs centreret header --}}
        <header class="text-center">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 mx-auto rounded-full object-cover ring-2 ring-amber-300 ring-offset-[6px] ring-offset-blue-950 mb-5">
            @endif
            <h1 class="text-4xl font-bold tracking-[0.12em] uppercase text-amber-200">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 text-base italic text-blue-200">{{ $user->job_title }}</p>
            @endif
            <div class="mt-5 flex items-center justify-center gap-4 text-sm text-blue-200 flex-wrap">
                @if ($user->phone)
                    <span>{{ $user->phone }}</span>
                    <span class="text-amber-300">✦</span>
                @endif
                <span class="break-all">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span class="text-amber-300">✦</span>
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span class="text-amber-300">✦</span>
                    <span>{{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>
            <div class="mt-6 flex items-center gap-3">
                <span class="flex-1 h-px bg-linear-to-r from-transparent via-amber-300/60 to-transparent"></span>
                <span class="text-amber-300 text-xs">❖</span>
                <span class="flex-1 h-px bg-linear-to-r from-transparent via-amber-300/60 to-transparent"></span>
            </div>
        </header>

        <div class="mt-10 grid grid-cols-[1fr_52mm] gap-12 flex-1">
            {{-- Erhvervserfaring --}}
            <main>
                <h2 class="text-sm font-bold uppercase tracking-[0.3em] text-amber-200 text-center border-b border-amber-300/40 pb-3">Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-6 space-y-7">
                    @foreach ($jobs as $job)
                        <article>
                            <p class="text-xs uppercase tracking-[0.2em] text-amber-300">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                –
                                {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-1 text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                            <p class="text-sm italic text-blue-300">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-sm leading-relaxed text-blue-100/85">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </main>

            {{-- Kompetencer --}}
            @if ($skills)
                <aside>
                    <h2 class="text-sm font-bold uppercase tracking-[0.3em] text-amber-200 text-center border-b border-amber-300/40 pb-3">Kompetencer</h2>
                    <ul class="mt-6 space-y-2.5 text-sm text-blue-100">
                        @foreach ($skills as $skill)
                            <li class="flex items-start gap-2.5">
                                <span class="text-amber-300 mt-0.5">◆</span>
                                {{ $skill }}
                            </li>
                        @endforeach
                    </ul>
                </aside>
            @endif
        </div>

        {{-- Diskret footer --}}
        <footer class="mt-10 border-t border-amber-300/25 pt-4 text-center text-xs italic text-blue-300/70">
            {{ $user->name }} — Curriculum Vitae
        </footer>
    </div>
</body>
</html>