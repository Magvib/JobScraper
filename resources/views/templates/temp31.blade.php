{{-- Bauhaus Geometric --}}
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
        .bauhaus-triangle {
            width: 0; height: 0;
            border-left: 9mm solid transparent;
            border-right: 9mm solid transparent;
            border-bottom: 15mm solid #2563eb;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#f6f4ee] shadow-lg overflow-hidden min-h-[297mm] font-sans text-neutral-900 flex flex-col">

        {{-- Geometrisk header --}}
        <header class="bg-white border-b-4 border-neutral-900 px-12 pt-12 pb-10 relative overflow-hidden">
            <span class="absolute -right-10 -top-14 w-44 h-44 rounded-full bg-red-500"></span>
            <span class="absolute right-28 top-16 bauhaus-triangle inline-block"></span>
            <div class="relative">
                <p class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-600">Form · Farve · Funktion</p>
                <h1 class="mt-3 text-5xl font-black tracking-tight leading-none">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-3 text-lg font-medium text-neutral-500">{{ $user->job_title }}</p>
                @endif
            </div>
        </header>

        {{-- Farvebjælke-kontakt --}}
        <div class="grid grid-cols-4 text-center text-[10px] font-bold uppercase tracking-wider text-white">
            @if ($user->phone)
                <div class="bg-red-500 py-2.5">{{ $user->phone }}</div>
            @endif
            <div class="bg-blue-600 py-2.5 break-all px-2">{{ $user->email }}</div>
            @if ($user->address || $user->city)
                <div class="bg-yellow-400 text-neutral-900 py-2.5 px-2">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</div>
            @endif
            @if ($user->birthdate)
                <div class="bg-neutral-900 py-2.5">{{ $user->birthdate->format('d/m/Y') }}</div>
            @endif
        </div>

        <div class="flex-1 px-12 py-12 grid grid-cols-[1fr_56mm] gap-12">
            {{-- Erhvervserfaring med geometriske markører --}}
            <main>
                <h2 class="flex items-center gap-3 text-sm font-black uppercase tracking-[0.25em] border-b-2 border-neutral-900 pb-3">
                    <span class="w-4 h-4 rounded-full bg-red-500 inline-block"></span>
                    Erhvervserfaring &amp; Uddannelse
                </h2>
                <div class="mt-7 space-y-7">
                    @foreach ($jobs as $i => $job)
                        <article class="flex gap-5">
                            <span class="shrink-0 w-2 {{ ['bg-red-500', 'bg-blue-600', 'bg-yellow-400', 'bg-neutral-900'][$i % 4] }} h-auto block"></span>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.25em] text-neutral-400">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    —
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 text-xl font-bold leading-tight">{{ $job['title'] }}</h3>
                                <p class="text-sm font-medium text-neutral-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-neutral-600">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </main>

            {{-- Kompetencer i gitter --}}
            @if ($skills)
                <aside>
                    <h2 class="flex items-center gap-3 text-sm font-black uppercase tracking-[0.25em] border-b-2 border-neutral-900 pb-3">
                        <span class="w-4 h-4 bg-yellow-400 inline-block rotate-45"></span>
                        Kompetencer
                    </h2>
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}"
                             class="mt-6 w-28 h-28 object-cover border-2 border-neutral-900">
                    @endif
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        @foreach ($skills as $skill)
                            <span class="text-xs font-bold bg-white border border-neutral-900 px-3 py-2 text-center leading-4">{{ $skill }}</span>
                        @endforeach
                    </div>
                </aside>
            @endif
        </div>
    </div>
</body>
</html>