{{-- Vintage Typewriter --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#faf3e3] shadow-lg min-h-[297mm] font-mono text-stone-800 px-12 py-12">

        {{-- Vintage header med ramme --}}
        <header class="border-2 border-double border-stone-700 px-8 py-8 relative">
            <span class="absolute -top-3.5 left-8 bg-[#faf3e3] px-3 text-xs font-bold uppercase tracking-[0.35em] text-stone-700">Curriculum Vitae</span>
            <div class="flex items-center gap-6">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-20 h-20 object-cover border-2 border-stone-700 p-1 bg-[#faf3e3] shrink-0 sepia-[0.35]">
                @endif
                <div>
                    <h1 class="text-3xl font-bold uppercase tracking-widest text-stone-800">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1 text-sm text-stone-600">{{ $user->job_title }}</p>
                    @endif
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-x-4 gap-y-1 text-xs text-stone-600 leading-5">
                @if ($user->phone)
                    <span>Tlf: {{ $user->phone }}</span>
                @endif
                <span class="break-all">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>
        </header>

        {{-- Erhvervserfaring --}}
        @if ($jobs)
            <section class="mt-10">
                <h2 class="text-sm font-bold uppercase tracking-[0.3em] text-stone-700 border-b border-dashed border-stone-500 pb-2">§ 1 — Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article>
                            <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                <h3 class="font-bold text-base uppercase tracking-wide">{{ $job['title'] }}</h3>
                                <p class="text-xs text-stone-500">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    —
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="mt-1 text-sm text-stone-600 italic">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-sm leading-relaxed text-stone-700">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Kompetencer --}}
        @if ($skills)
            <section class="mt-10">
                <h2 class="text-sm font-bold uppercase tracking-[0.3em] text-stone-700 border-b border-dashed border-stone-500 pb-2">§ 2 — Kompetencer</h2>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-xs font-bold text-stone-700 border border-stone-600 px-3 py-1 bg-white/40">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Signatur --}}
        <p class="mt-12 text-right text-xs text-stone-500 italic">underskrevet, {{ $user->name }}</p>
    </div>
</body>
</html>