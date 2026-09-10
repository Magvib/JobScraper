{{-- Warm Magazine --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-orange-50 shadow-lg overflow-hidden grid grid-cols-[70mm_1fr] min-h-[297mm] font-sans text-gray-900">

        {{-- Venstre billedkolonne --}}
        <aside class="bg-orange-200 relative flex flex-col">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-[110mm] object-cover">
            @else
                <div class="w-full h-[110mm] bg-orange-300"></div>
            @endif

            {{-- Kontakt --}}
            <section class="px-7 py-8 flex-1 flex flex-col gap-7">
                <div>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.25em] text-orange-800 border-b border-orange-400/60 pb-2">Kontakt</h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        @if ($user->phone)
                            <li>
                                <p class="text-[10px] uppercase tracking-wider text-orange-700/80 font-semibold">Telefon</p>
                                <p class="mt-0.5 font-medium">{{ $user->phone }}</p>
                            </li>
                        @endif
                        <li>
                            <p class="text-[10px] uppercase tracking-wider text-orange-700/80 font-semibold">E-mail</p>
                            <p class="mt-0.5 font-medium break-all">{{ $user->email }}</p>
                        </li>
                        @if ($user->address || $user->city)
                            <li>
                                <p class="text-[10px] uppercase tracking-wider text-orange-700/80 font-semibold">Adresse</p>
                                <p class="mt-0.5 font-medium">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                            </li>
                        @endif
                    </ul>
                </div>

                {{-- Personligt --}}
                @if ($user->birthdate)
                    <div>
                        <h2 class="text-[10px] font-bold uppercase tracking-[0.25em] text-orange-800 border-b border-orange-400/60 pb-2">Personligt</h2>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li>
                                <p class="text-[10px] uppercase tracking-wider text-orange-700/80 font-semibold">Fødselsdato</p>
                                <p class="mt-0.5 font-medium">{{ $user->birthdate->format('d/m/Y') }}</p>
                            </li>
                        </ul>
                    </div>
                @endif
            </section>
        </aside>

        {{-- Højre indhold --}}
        <main class="bg-white px-10 py-12">
            <header>
                @if ($user->job_title)
                    <p class="text-xs font-bold uppercase tracking-[0.3em] text-orange-500">{{ $user->job_title }}</p>
                @endif
                <h1 class="mt-2 text-4xl font-extrabold tracking-tight leading-tight">{{ $user->name }}</h1>
            </header>

            {{-- Kompetencer --}}
            @if ($skills)
                <section class="mt-8">
                    <h2 class="text-xs font-bold uppercase tracking-[0.25em] text-orange-500 border-b-2 border-orange-100 pb-2">Kompetencer</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-xs font-semibold text-orange-700 bg-orange-100 px-3 py-1 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Erhvervserfaring & uddannelse --}}
            @if ($jobs)
                <section class="mt-8">
                    <h2 class="text-xs font-bold uppercase tracking-[0.25em] text-orange-500 border-b-2 border-orange-100 pb-2">Erhvervserfaring &amp; Uddannelse</h2>
                    <div class="mt-5 space-y-6">
                        @foreach ($jobs as $job)
                            <article>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-orange-400">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm font-medium text-slate-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </main>
    </div>
</body>
</html>