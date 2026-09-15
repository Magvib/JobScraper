{{-- Barcode --}}
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
        /* Stregkode: varierende lodrette streger */
        .barcode {
            background: repeating-linear-gradient(90deg,
                #111 0 2px, transparent 2px 4px,
                #111 4px 5px, transparent 5px 8px,
                #111 8px 11px, transparent 11px 12px,
                #111 12px 13px, transparent 13px 17px,
                #111 17px 19px, transparent 19px 20px,
                #111 20px 23px, transparent 23px 24px,
                #111 24px 25px, transparent 25px 29px);
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-mono text-neutral-900 px-14 py-12 overflow-hidden">

        <header class="flex items-start justify-between gap-8">
            <div>
                <p class="text-[10px] uppercase tracking-[0.4em] text-neutral-400">SKU: PROFIL-{{ strtoupper(substr(md5($user->name ?? ''), 0, 8)) }}</p>
                <h1 class="mt-3 text-5xl font-bold uppercase tracking-tight leading-none">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2.5 text-base font-semibold uppercase tracking-widest text-neutral-500">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover grayscale-40 border-2 border-neutral-900 shrink-0">
            @endif
        </header>

        {{-- Stregkode-bånd med menneskelæselig tekst --}}
        <div class="mt-6 flex items-end gap-6">
            <div class="barcode h-12 flex-1"></div>
        </div>
        <p class="mt-2 text-[11px] tracking-[0.5em] text-neutral-600 uppercase">
            {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
        </p>

        @if (isset($coverLetter))
            <section class="mt-9">
                <h2 class="inline-block bg-neutral-900 text-white text-xs font-bold uppercase tracking-[0.3em] px-4 py-2">Ansøgning</h2>
                <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-neutral-700">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-9">
                <h2 class="inline-block bg-neutral-900 text-white text-xs font-bold uppercase tracking-[0.3em] px-4 py-2">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 space-y-5">
                    @foreach ($jobs as $i => $job)
                        <article class="border border-neutral-300 px-6 py-4 flex gap-6">
                            <div class="shrink-0 border-r border-neutral-200 pr-5 text-center">
                                <p class="text-[10px] uppercase tracking-widest text-neutral-400">Vare</p>
                                <p class="text-2xl font-bold leading-none mt-1 tabular-nums">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</p>
                                <div class="barcode h-6 mt-2 w-14"></div>
                            </div>
                            <div class="flex-1">
                                <p class="text-[11px] font-bold tracking-widest text-neutral-400 tabular-nums uppercase">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-bold text-lg leading-snug uppercase">{{ $job['title'] }}</h3>
                                <p class="text-sm font-semibold text-neutral-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-neutral-600">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="inline-block bg-neutral-900 text-white text-xs font-bold uppercase tracking-[0.3em] px-4 py-2">Kompetencer · indhold</h2>
                <div class="mt-6 grid grid-cols-2 gap-x-10">
                    @foreach ($skills as $skill)
                        <p class="text-sm font-semibold text-neutral-700 border-b border-dotted border-neutral-300 pb-1.5 flex justify-between">
                            {{ $skill }} <span class="text-neutral-400 tabular-nums">✓</span>
                        </p>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-12 flex items-end justify-between">
            <div>
                <div class="barcode h-10 w-56"></div>
                <p class="mt-1.5 text-[10px] tracking-[0.35em] text-neutral-500 uppercase">{{ strtoupper(preg_replace('/[^A-Z0-9]/i', '', $user->name ?? '')) ?: 'SCAN-MIG' }}</p>
            </div>
            <p class="text-[10px] uppercase tracking-widest text-neutral-400">Scannet · {{ now()->format('d.m.Y H:i') }}</p>
        </footer>
    </div>
</body>
</html>