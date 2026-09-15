{{-- Drafting Table --}}
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
        /* Tegnestue: lineal-kant øverst med millimeterstreger */
        .ruler {
            background: repeating-linear-gradient(90deg,
                #0c4a6e 0 1px, transparent 1px 5px,
                #0c4a6e 5px 6px, transparent 6px 10px);
            background-size: 100% 12px;
            background-repeat: no-repeat;
        }
        /* Mållinje med pile i begge ender */
        .dim {
            position: relative;
            height: 1px;
            background: #0c4a6e;
        }
        .dim::before, .dim::after {
            content: '';
            position: absolute;
            top: -4px;
            border: 4.5px solid transparent;
        }
        .dim::before { left: -1px; border-left: 7px solid #0c4a6e; border-right: none; }
        .dim::after  { right: -1px; border-right: 7px solid #0c4a6e; border-left: none; }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#f0f6fb] shadow-xl min-h-[297mm] font-mono text-sky-950 overflow-hidden">

        {{-- Lineal-kant --}}
        <div class="ruler bg-sky-50 h-12 border-b-2 border-sky-800"></div>

        <div class="px-14 py-10">
            <header class="relative">
                <div class="flex items-start gap-8">
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover border-2 border-sky-800 shrink-0" style="clip-path: polygon(0 0, 100% 0, 100% 85%, 85% 100%, 0 100%);">
                    @endif
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.4em] text-sky-600">Tegning nr. 1 · Curriculum Vitae</p>
                        <h1 class="mt-3 text-4xl font-bold uppercase tracking-wide text-sky-950">{{ $user->name }}</h1>
                        @if ($user->job_title)
                            <p class="mt-2 text-sm font-bold uppercase tracking-widest text-sky-700">{{ $user->job_title }}</p>
                        @endif
                    </div>
                </div>
                <div class="mt-6 dim w-48"></div>
                <p class="mt-2 text-[11px] text-sky-600 uppercase tracking-widest">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
                </p>
            </header>

            @if (isset($coverLetter))
                <section class="mt-9">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.35em] text-sky-800 bg-sky-50 border border-sky-800 px-3 py-1.5 inline-block">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[14px] leading-relaxed text-sky-900 bg-white border border-sky-200 px-6 py-5">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-9">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.35em] text-sky-800 bg-sky-50 border border-sky-800 px-3 py-1.5 inline-block">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $i => $job)
                            <article class="relative bg-white border border-sky-800 px-6 py-5">
                                {{-- Mållinje med indeksnummer --}}
                                <span class="absolute -top-3 left-5 bg-sky-800 text-white text-[10px] font-bold px-2 py-0.5 tracking-widest">POS {{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                    <h3 class="font-bold text-lg uppercase tracking-wide text-sky-950">{{ $job['title'] }}</h3>
                                    <p class="text-[11px] font-bold text-sky-600 tabular-nums uppercase whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-sky-700 mt-0.5">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-sky-800/80">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.35em] text-sky-800 bg-sky-50 border border-sky-800 px-3 py-1.5 inline-block">Kompetencer · stykliste</h2>
                    <div class="mt-5 bg-white border border-sky-200 px-6 py-4 space-y-1.5">
                        @foreach ($skills as $skill)
                            <p class="text-[13px] font-semibold text-sky-900 flex justify-between border-b border-dotted border-sky-300 pb-1.5">
                                <span>{{ $skill }}</span>
                                <span class="text-sky-400">REF {{ str_pad((string) ($loop->index + 1), 3, '0', STR_PAD_LEFT) }}</span>
                            </p>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Tegnestue-titelblok i bunden --}}
            <footer class="mt-10 border-2 border-sky-800 grid grid-cols-4 divide-x divide-sky-800 text-[10px] uppercase tracking-widest">
                <div class="px-4 py-2.5 bg-sky-50"><p class="text-sky-500">Tegnet af</p><p class="font-bold mt-1 truncate">{{ $user->name }}</p></div>
                <div class="px-4 py-2.5 bg-sky-50"><p class="text-sky-500">Dato</p><p class="font-bold mt-1">{{ now()->format('d.m.Y') }}</p></div>
                <div class="px-4 py-2.5 bg-sky-50"><p class="text-sky-500">Blad</p><p class="font-bold mt-1">A4 · 1/1</p></div>
                <div class="px-4 py-2.5 bg-sky-800 text-white"><p class="text-sky-200">Godkendt</p><p class="font-bold mt-1">✓</p></div>
            </footer>
        </div>
    </div>
</body>
</html>