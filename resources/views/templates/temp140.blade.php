{{-- Bento Header --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($coverLetter) ? 'Letter - ' : 'CV - ' }}{{ $user->name }}</title>
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
    <div class="cv-page max-w-[210mm] mx-auto bg-stone-100 shadow-xl min-h-[297mm] font-sans text-stone-800 p-10 overflow-hidden">

        {{-- Bento-gitter i toppen: navneflise, fotoflise, kontaktflese, kompetenceflise --}}
        <header class="grid grid-cols-6 grid-rows-[auto_auto_auto] gap-4">
            <div class="col-span-4 bg-stone-900 text-white rounded-2xl px-8 py-7">
                <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-base font-semibold text-rose-300">{{ $user->job_title }}</p>
                @endif
            </div>
            <div class="col-span-2 row-span-2 bg-white rounded-2xl p-2 shadow-sm">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-xl">
                @else
                    <div class="w-full h-full min-h-[120px] bg-stone-200 rounded-xl flex items-center justify-center text-4xl font-black text-stone-400">
                        {{ mb_strtoupper(mb_substr((string) $user->name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="col-span-2 bg-white rounded-2xl px-6 py-5 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-stone-400">Kontakt</p>
                <div class="mt-2 text-[13px] leading-relaxed text-stone-600">
                    @if ($user->phone)
                        <p>{{ $user->phone }}</p>
                    @endif
                    <p class="break-all">{{ $user->email }}</p>
                    @if ($user->address || $user->city)
                        <p>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                    @endif
                    @if ($user->birthdate)
                        <p>Født {{ $user->birthdate->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>
            @if ($skills)
                <div class="col-span-4 bg-rose-200/70 rounded-2xl px-6 py-5">
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-rose-900/60">Kompetencer</p>
                    <p class="mt-2 text-[13px] font-semibold text-rose-950 leading-relaxed">
                        {{ implode('  ·  ', array_slice($skills, 0, 8)) }}
                    </p>
                </div>
            @else
                <div class="col-span-4 bg-rose-200/70 rounded-2xl px-6 py-5">
                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-rose-900/60">Kontakt</p>
                    <p class="mt-2 text-[13px] font-semibold text-rose-950 break-all">{{ $user->email }}</p>
                </div>
            @endif
        </header>

        {{-- Indhold i én stor hvid bento-flise --}}
        <div class="mt-4 bg-white rounded-2xl shadow-sm px-9 py-8 min-h-[150mm]">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-stone-900 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-md bg-rose-400"></span> Ansøgning
                        <span class="flex-1 h-px bg-stone-200"></span>
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-stone-900 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-md bg-rose-400"></span> Erhvervserfaring &amp; uddannelse
                        <span class="flex-1 h-px bg-stone-200"></span>
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5">
                                <div class="shrink-0 bg-stone-100 rounded-xl px-4 py-3 text-center self-start">
                                    <p class="text-lg font-extrabold text-stone-900 tabular-nums leading-none">{{ \Carbon\Carbon::parse($job['startDate'])->format('Y') }}</p>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-stone-400 mt-1">
                                        – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('Y') : 'nu' }}
                                    </p>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-rose-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-10 pt-4 border-t border-stone-100 flex justify-between text-xs text-stone-400 tracking-wide">
                <span>{{ $user->name }}</span>
                <span>{{ now()->format('Y') }}</span>
            </footer>
        </div>
    </div>
</body>
</html>