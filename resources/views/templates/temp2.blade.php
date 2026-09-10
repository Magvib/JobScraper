{{-- Dark Sidebar --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg overflow-hidden grid grid-cols-[60mm_1fr] min-h-[297mm] font-sans text-gray-900">

        {{-- Sidebar --}}
        <aside class="bg-slate-900 text-slate-100 px-7 py-12 flex flex-col gap-8">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-32 h-32 mx-auto rounded-full object-cover ring-4 ring-white/15">
            @endif

            {{-- Kontakt --}}
            <section>
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 border-b border-white/10 pb-2">Kontakt</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    @if ($user->address || $user->city)
                        <li>
                            <p class="text-slate-400 text-xs uppercase tracking-wide">Adresse</p>
                            <p class="mt-0.5">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                        </li>
                    @endif
                    @if ($user->phone)
                        <li>
                            <p class="text-slate-400 text-xs uppercase tracking-wide">Telefon</p>
                            <p class="mt-0.5">{{ $user->phone }}</p>
                        </li>
                    @endif
                    <li>
                        <p class="text-slate-400 text-xs uppercase tracking-wide">E-mail</p>
                        <p class="mt-0.5 break-all">{{ $user->email }}</p>
                    </li>
                </ul>
            </section>

            {{-- Kompetencer --}}
            @if ($skills)
                <section>
                    <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 border-b border-white/10 pb-2">Kompetencer</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-xs font-medium bg-white/10 px-3 py-1 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Personlige oplysninger --}}
            <section>
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 border-b border-white/10 pb-2">Personligt</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    @if ($user->birthdate)
                        <li>
                            <p class="text-slate-400 text-xs uppercase tracking-wide">Fødselsdato</p>
                            <p class="mt-0.5">{{ $user->birthdate->format('d/m/Y') }}</p>
                        </li>
                    @endif
                </ul>
            </section>
        </aside>

        {{-- Hovedindhold --}}
        <main class="px-12 py-12">
            <header>
                <h1 class="text-4xl font-bold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg text-teal-600 font-medium">{{ $user->job_title }}</p>
                @endif
            </header>

            {{-- Erhvervserfaring & uddannelse --}}
            @if ($jobs)
                <section class="mt-10">
                    <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-teal-600 border-b-2 border-slate-900 pb-2">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-5 border-l-2 border-slate-200 space-y-6">
                        @foreach ($jobs as $job)
                            <div class="relative pl-6">
                                <span class="absolute -left-1.25 top-1.5 w-2 h-2 rounded-full bg-teal-500"></span>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
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
    </div>
</body>
</html>