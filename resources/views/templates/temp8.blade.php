{{-- Dark Tech --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-gray-950 text-gray-100 shadow-lg overflow-hidden min-h-[297mm] font-sans flex flex-col">

        {{-- Mørk header med cyan-accent --}}
        <header class="px-12 pt-12 pb-10 flex items-center gap-8 border-b border-white/10">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 rounded-full object-cover ring-2 ring-cyan-400 ring-offset-4 ring-offset-gray-950 shrink-0">
            @endif
            <div>
                @if ($user->job_title)
                    <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-cyan-400">{{ $user->job_title }}</p>
                @endif
                <h1 class="mt-2 text-4xl font-bold tracking-tight text-white">{{ $user->name }}</h1>
                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-400">
                    @if ($user->phone)
                        <span>{{ $user->phone }}</span>
                    @endif
                    <span class="break-all">{{ $user->email }}</span>
                    @if ($user->address || $user->city)
                        <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                    @endif
                    @if ($user->birthdate)
                        <span>{{ $user->birthdate->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
        </header>

        {{-- Kompetencer som outline-chips --}}
        @if ($skills)
            <section class="px-12 py-5 flex flex-wrap items-center gap-2 border-b border-white/10">
                <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-cyan-400 mr-3">Kompetencer</span>
                @foreach ($skills as $skill)
                    <span class="text-xs font-medium text-gray-200 border border-cyan-400/40 px-3 py-1 rounded-full">{{ $skill }}</span>
                @endforeach
            </section>
        @endif

        {{-- Erfaring som mørke kort --}}
        <main class="flex-1 px-12 py-10 grid gap-5 content-start">
            <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-cyan-400">Erhvervserfaring &amp; Uddannelse</h2>
            @foreach ($jobs as $job)
                <article class="bg-white/5 border border-white/10 rounded-lg px-6 py-5">
                    <div class="flex items-baseline justify-between gap-4">
                        <h3 class="font-bold text-lg leading-snug text-white">{{ $job['title'] }}</h3>
                        <p class="text-xs font-semibold text-cyan-400 uppercase tracking-wide whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                            –
                            {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                        </p>
                    </div>
                    <p class="mt-0.5 text-sm font-medium text-gray-400">{{ $job['company'] }}</p>
                    @if (!empty($job['description']))
                        <p class="mt-2 text-sm leading-relaxed text-gray-300">{{ $job['description'] }}</p>
                    @endif
                </article>
            @endforeach
        </main>

        {{-- Diskret footer --}}
        <footer class="px-12 py-5 border-t border-white/10 flex justify-between text-[10px] uppercase tracking-[0.3em] text-gray-500">
            <span>{{ $user->name }}</span>
            <span>{{ $user->job_title }}</span>
        </footer>
    </div>
</body>
</html>