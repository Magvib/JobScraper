{{-- Dark Sidebar Right --}}
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
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg overflow-hidden grid grid-cols-[1fr_60mm] min-h-[297mm] font-sans text-gray-900">

        {{-- Hovedindhold til venstre --}}
        <main class="px-12 py-12">
            <header>
                <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-orange-500">Curriculum Vitae</p>
                <h1 class="mt-3 text-4xl font-bold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg text-orange-600 font-medium">{{ $user->job_title }}</p>
                @endif
            </header>

            {{-- Erhvervserfaring --}}
            @if ($jobs)
                <section class="mt-10">
                    <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-gray-900 border-b-2 border-orange-500 pb-2">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-5 space-y-6">
                        @foreach ($jobs as $job)
                            <div class="border-l-2 border-orange-200 pl-5">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm font-medium text-gray-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-gray-700">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </main>

        {{-- Mørk sidebar til højre --}}
        <aside class="bg-gray-900 text-gray-100 px-7 py-12 flex flex-col gap-8">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-32 h-32 mx-auto rounded-2xl object-cover ring-4 ring-orange-500/40">
            @endif

            {{-- Kontakt --}}
            <section>
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400 border-b border-white/10 pb-2">Kontakt</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    @if ($user->address || $user->city)
                        <li>
                            <p class="text-gray-400 text-xs uppercase tracking-wide">Adresse</p>
                            <p class="mt-0.5">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                        </li>
                    @endif
                    @if ($user->phone)
                        <li>
                            <p class="text-gray-400 text-xs uppercase tracking-wide">Telefon</p>
                            <p class="mt-0.5">{{ $user->phone }}</p>
                        </li>
                    @endif
                    <li>
                        <p class="text-gray-400 text-xs uppercase tracking-wide">E-mail</p>
                        <p class="mt-0.5 break-all">{{ $user->email }}</p>
                    </li>
                </ul>
            </section>

            {{-- Kompetencer --}}
            @if ($skills)
                <section>
                    <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400 border-b border-white/10 pb-2">Kompetencer</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-xs font-medium bg-orange-500/20 text-orange-300 px-3 py-1 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Personligt --}}
            @if ($user->birthdate)
                <section>
                    <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400 border-b border-white/10 pb-2">Personligt</h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li>
                            <p class="text-gray-400 text-xs uppercase tracking-wide">Fødselsdato</p>
                            <p class="mt-0.5">{{ $user->birthdate->format('d/m/Y') }}</p>
                        </li>
                    </ul>
                </section>
            @endif
        </aside>
    </div>
</body>
</html>