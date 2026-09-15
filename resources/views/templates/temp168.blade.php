{{-- Sticker Sheet --}}
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
        /* Udklipnings-klistermærker: hvid die-cut kant + blød skygge som på et ark */
        .diecut {
            box-shadow: 0 1px 2px rgba(0,0,0,.18), 0 4px 10px rgba(0,0,0,.10);
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
    $tilts = ['rotate-[-2deg]', 'rotate-1', 'rotate-2', '-rotate-1', '-rotate-2', 'rotate-[-1.5deg]'];
    $fills = ['bg-pink-500 text-white', 'bg-sky-500 text-white', 'bg-amber-400 text-amber-950', 'bg-lime-500 text-lime-950', 'bg-violet-500 text-white'];
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-stone-100 shadow-xl min-h-[297mm] font-sans text-stone-800 px-12 py-12 overflow-hidden">

        <header class="flex items-end justify-between gap-8 flex-wrap">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.45em] text-stone-400">Klistermærke-ark · CV</p>
                <h1 class="mt-3 text-5xl font-black tracking-tighter text-stone-900 leading-none">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-3 text-lg font-bold text-pink-600">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-28 h-28 object-cover rounded-full ring-4 ring-white diecut -rotate-2 shrink-0">
            @endif
        </header>

        {{-- Kontakt som aflangt mærke --}}
        <div class="mt-6">
            <p class="diecut inline-block bg-white rounded-full px-6 py-2.5 text-sm font-semibold text-stone-600 rotate-1">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
            </p>
        </div>

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="diecut inline-block bg-stone-900 text-white text-xs font-black uppercase tracking-[0.3em] rounded-full px-5 py-2 -rotate-1">Ansøgning</h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-700">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="diecut inline-block bg-stone-900 text-white text-xs font-black uppercase tracking-[0.3em] rounded-full px-5 py-2 rotate-1">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 grid grid-cols-2 gap-6">
                    @foreach ($jobs as $i => $job)
                        <article class="diecut bg-white rounded-2xl px-6 py-5 {{ $tilts[$i % count($tilts)] }}">
                            <p class="text-[11px] font-black tracking-widest text-stone-400 tabular-nums uppercase">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-1 font-extrabold text-lg leading-snug text-stone-900">{{ $job['title'] }}</h3>
                            <p class="text-sm font-semibold text-stone-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="diecut inline-block bg-white text-stone-900 text-xs font-black uppercase tracking-[0.3em] rounded-full px-5 py-2 border-2 border-dashed border-stone-400 rotate-1">Kompetencer · klip ud</h2>
                <div class="mt-5 flex flex-wrap gap-3.5">
                    @foreach ($skills as $skill)
                        <span class="diecut {{ $fills[$loop->index % count($fills)] }} {{ $tilts[$loop->index % count($tilts)] }} text-[14px] font-black rounded-lg px-4 py-2 -mx-px">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-12 flex justify-between items-end">
            <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-stone-400">{{ $user->name }}</p>
            <p class="text-[9px] font-mono uppercase tracking-widest text-stone-300">✂ - - - - - - - - - - klip langs stregen</p>
        </footer>
    </div>
</body>
</html>