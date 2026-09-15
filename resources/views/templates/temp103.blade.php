{{-- Emerald Ledger --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ $user->name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .cv-page { box-shadow: none !important; margin: 0 !important; max-width: 100% !important; }
        }
        .cv-page { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] text-emerald-950 overflow-hidden">

        {{-- Konsulent-header med tykk emerald linje --}}
        <header class="px-14 pt-12 pb-8 border-b-[3px] border-emerald-700 flex items-start justify-between gap-8">
            <div>
                <h1 class="text-5xl font-serif font-bold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg font-serif italic text-emerald-700">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 object-cover shrink-0 rounded-none border-2 border-emerald-700 shadow-[6px_6px_0_0_rgba(4,120,87,0.25)]">
            @endif
        </header>

        <div class="px-14 py-9 grid grid-cols-[1fr_220px] gap-10">
            {{-- Venstre kolonne: indhold --}}
            <div>
                @if (isset($coverLetter))
                    <section>
                        <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-emerald-800 pb-2 border-b border-emerald-200">Ansøgning</h2>
                        <div class="mt-5 space-y-4 text-[15px] leading-relaxed font-sans">
                            {!! $coverLetter->renderContext() !!}
                        </div>
                    </section>
                @elseif ($jobs)
                    <section>
                        <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-emerald-800 pb-2 border-b border-emerald-200 font-sans">Erhvervserfaring &amp; uddannelse</h2>
                        <div class="mt-6 space-y-7">
                            @foreach ($jobs as $job)
                                <article>
                                    <div class="flex items-baseline justify-between gap-4">
                                        <h3 class="text-xl font-serif font-bold leading-snug">{{ $job['title'] }}</h3>
                                        <p class="shrink-0 text-xs font-sans font-bold tracking-widest text-emerald-700 tabular-nums">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                    </div>
                                    <p class="mt-0.5 text-sm font-sans font-semibold uppercase tracking-wider text-emerald-600">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2.5 leading-relaxed font-sans text-emerald-950/80 text-justify">{{ $job['description'] }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($skills)
                    <section class="mt-10">
                        <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-emerald-800 pb-2 border-b border-emerald-200 font-sans">Kompetencer</h2>
                        <p class="mt-4 text-sm font-sans font-medium leading-loose">
                            {{ implode('  ·  ', $skills) }}
                        </p>
                    </section>
                @endif
            </div>

            {{-- Højre kolonne: faktaboks --}}
            <aside class="border-l-2 border-emerald-700 pl-6 font-sans self-start">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-emerald-700">Oplysninger</h2>
                <dl class="mt-4 space-y-4 text-[13px]">
                    <div>
                        <dt class="font-bold uppercase tracking-wider text-[10px] text-emerald-950/60">Telefon</dt>
                        @if ($user->phone)
                            <dd class="mt-0.5">{{ $user->phone }}</dd>
                        @endif
                    </div>
                    <div>
                        <dt class="font-bold uppercase tracking-wider text-[10px] text-emerald-950/60">E-mail</dt>
                        <dd class="mt-0.5 break-all">{{ $user->email }}</dd>
                    </div>
                    @if ($user->address || $user->city)
                        <div>
                            <dt class="font-bold uppercase tracking-wider text-[10px] text-emerald-950/60">Adresse</dt>
                            <dd class="mt-0.5 leading-snug">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</dd>
                        </div>
                    @endif
                    @if ($user->birthdate)
                        <div>
                            <dt class="font-bold uppercase tracking-wider text-[10px] text-emerald-950/60">Fødselsdato</dt>
                            <dd class="mt-0.5">{{ $user->birthdate->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </aside>
        </div>
    </div>
</body>
</html>
