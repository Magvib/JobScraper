{{-- Indigo Banner --}}
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
@endphp
<body class="bg-white min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg overflow-hidden min-h-[297mm] font-sans text-gray-900">

        {{-- Farvet topbanner --}}
        <header class="bg-linear-to-r from-indigo-600 to-violet-600 text-white px-12 pt-12 pb-12 flex items-center gap-8">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 rounded-full object-cover ring-4 ring-white/30 shrink-0">
            @endif
            <div>
                <h1 class="text-4xl font-bold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1 text-lg font-medium text-indigo-100">{{ $user->job_title }}</p>
                @endif
                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-1 text-sm text-indigo-100">
                    @if ($user->phone)
                        <span>📞 {{ $user->phone }}</span>
                    @endif
                    <span class="break-all">✉️ {{ $user->email }}</span>
                    @if ($user->address || $user->city)
                        <span>📍 {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                    @endif
                </div>
            </div>
        </header>

        {{-- Hovedindhold: erfaring + sidebar --}}
        <div class="grid grid-cols-[1fr_58mm]">
            <main class="px-12 py-10">
                @if ($jobs)
                    <section>
                        <h2 class="text-sm font-bold uppercase tracking-[0.25em] text-indigo-600">Erhvervserfaring &amp; uddannelse</h2>
                        <div class="mt-6 space-y-7">
                            @foreach ($jobs as $job)
                                <div class="border-l-4 border-indigo-200 pl-5">
                                    <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <h3 class="mt-1 font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-slate-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="mt-2 text-sm leading-relaxed text-slate-700">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </main>

            {{-- Hvid sidebar der overlapper banneret --}}
            <aside class="bg-indigo-50 px-6 py-10 flex flex-col gap-8">
                @if ($skills)
                    <section>
                        <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-700 border-b border-indigo-200 pb-2">Kompetencer</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($skills as $skill)
                                <span class="text-xs font-medium bg-indigo-600 text-white px-3 py-1 rounded-full">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section>
                    <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-700 border-b border-indigo-200 pb-2">Personligt</h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        @if ($user->birthdate)
                            <li>
                                <p class="text-slate-500 text-xs uppercase tracking-wide">Fødselsdato</p>
                                <p class="mt-0.5">{{ $user->birthdate->format('d/m/Y') }}</p>
                            </li>
                        @endif
                        @if ($user->address || $user->city)
                            <li>
                                <p class="text-slate-500 text-xs uppercase tracking-wide">Adresse</p>
                                <p class="mt-0.5">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                            </li>
                        @endif
                    </ul>
                </section>
            </aside>
        </div>
    </div>
</body>
</html>