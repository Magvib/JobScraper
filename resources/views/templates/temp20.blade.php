{{-- Editorial Chapters --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ $user->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4; margin: 0; }
        @media print {
            body { background: white !important; padding: 0 !important; }
            .cv-page { box-shadow: none !important; margin: 0 !important; max-width: 100% !important; }
        }
        .cv-page { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .outline-number {
            -webkit-text-stroke: 2px #e11d48;
            color: transparent;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg overflow-hidden min-h-[297mm] font-sans text-gray-900 flex flex-col">

        {{-- Header med foto helt højre --}}
        <header class="px-12 pt-12 pb-8 flex items-start justify-between gap-8 border-b-4 border-rose-600">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-rose-600">Curriculum Vitae</p>
                <h1 class="mt-3 text-5xl font-black tracking-tighter leading-none">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-3 text-lg text-slate-500">{{ $user->job_title }}</p>
                @endif>
                <div class="mt-5 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-600">
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
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-36 object-cover shrink-0 rounded-t-full border-2 border-rose-600">
            @endif
        </header>

        {{-- Kompetencer øverst i række --}}
        @if ($skills)
            <section class="px-12 py-5 flex flex-wrap items-center gap-2 border-b border-slate-200">
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-rose-600 mr-3">Kompetencer</span>
                @foreach ($skills as $skill)
                    <span class="text-xs font-semibold text-rose-700 bg-rose-50 px-3 py-1 rounded-full">{{ $skill }}</span>
                @endforeach
            </section>
        @endif

        {{-- Erhvervserfaring med kæmpenumre --}}
        <main class="flex-1 px-12 py-10">
            @if ($jobs)
                <h2 class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-6 space-y-7">
                    @foreach ($jobs as $i => $job)
                        <article class="flex gap-6 items-start">
                            <span class="outline-number text-7xl font-black leading-none shrink-0 select-none w-[1.6em]">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="pt-1.5 flex-1 border-b border-slate-100 pb-5">
                                <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                    <h3 class="text-xl font-extrabold tracking-tight leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold uppercase tracking-widest text-rose-600 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="mt-1 text-sm font-medium text-slate-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </main>

        <footer class="px-12 py-5 border-t-4 border-rose-600 flex justify-between items-baseline">
            <span class="text-lg font-black tracking-tight">{{ $user->name }}</span>
            <span class="text-[10px] font-black uppercase tracking-[0.4em] text-rose-600">Slut / End</span>
        </footer>
    </div>
</body>
</html>