{{-- Diagonal Forest --}}
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
        .diagonal-header { clip-path: polygon(0 0, 100% 0, 100% 78%, 0 100%); }
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg overflow-hidden min-h-[297mm] font-sans text-gray-900 flex flex-col">

        {{-- Diagonal skåret header --}}
        <header class="diagonal-header bg-emerald-900 text-white px-12 pt-12 pb-16 relative">
            <div class="flex items-center gap-8">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 rounded-full object-cover ring-4 ring-lime-300 shrink-0">
                @endif
                <div>
                    @if ($user->job_title)
                        <p class="text-xs font-bold uppercase tracking-[0.3em] text-lime-300">{{ $user->job_title }}</p>
                    @endif
                    <h1 class="mt-2 text-5xl font-black tracking-tight">{{ $user->name }}</h1>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-x-6 gap-y-1 text-sm text-emerald-100/90">
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
        </header>

        <div class="px-12 py-10 grid grid-cols-[1fr_56mm] gap-10 flex-1">
            {{-- Erhvervserfaring --}}
            <main>
                <h2 class="inline-block bg-lime-300 text-emerald-900 text-xs font-black uppercase tracking-[0.2em] px-3 py-1.5">Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="relative pl-5 border-l-2 border-emerald-900">
                            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-700">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                –
                                {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-1 font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                            <p class="text-sm font-medium text-emerald-600">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </main>

            <aside class="flex flex-col gap-8">
                {{-- Kompetencer --}}
                @if ($skills)
                    <section>
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-emerald-900 border-b-2 border-lime-300 pb-2">Kompetencer</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($skills as $skill)
                                <span class="text-xs font-bold text-emerald-900 bg-lime-100 border border-emerald-900/20 px-3 py-1">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Personligt --}}
                @if ($user->birthdate)
                    <section>
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-emerald-900 border-b-2 border-lime-300 pb-2">Personligt</h2>
                        <p class="mt-4 text-sm">
                            <span class="block text-[10px] uppercase tracking-wider text-stone-400 font-bold">Fødselsdato</span>
                            <span class="font-semibold">{{ $user->birthdate->format('d/m/Y') }}</span>
                        </p>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</body>
</html>