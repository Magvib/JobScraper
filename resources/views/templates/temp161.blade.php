{{-- Passport --}}
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
        /* Maskinlæselig zone som i rigtige pas */
        .mrz {
            font-family: ui-monospace, "SF Mono", Menlo, monospace;
            letter-spacing: 0.18em;
            font-size: 12px;
            line-height: 1.9;
            white-space: nowrap;
            overflow: hidden;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
    $mrzName = strtoupper(preg_replace('/[^A-Z0-9]/i', '<', $user->name ?? ''));
    $mrz1 = 'ID<DNK' . str_pad($mrzName, 39, '<') . '';
    $mrz2 = str_pad('', 44, '<');
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#e7e2d5] shadow-xl min-h-[297mm] font-sans text-stone-800 p-6 overflow-hidden">
        <div class="bg-[#f5f2e8] min-h-[285mm] border border-stone-400/60 shadow-inner relative">

            {{-- Mørkt pas-cover-band øverst --}}
            <header class="bg-stone-900 text-white px-12 pt-10 pb-9 rounded-t-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-amber-300">Kongeriget Danmark</p>
                        <h1 class="mt-3 text-3xl font-extrabold uppercase tracking-[0.2em]">Kompetencepas</h1>
                        <p class="mt-2 text-xs text-stone-300 tracking-widest uppercase">CV &amp; ansøgning · {{ now()->format('d.m.Y') }}</p>
                    </div>
                    <span class="text-amber-300 text-3xl leading-none">★</span>
                </div>
            </header>

            <div class="px-12 py-9">
                <div class="flex gap-9">
                    {{-- Foto- og datakolonne som pas-side 1 --}}
                    <div class="shrink-0 w-40">
                        @if ($photo)
                            <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-36 h-44 object-cover border border-stone-400 bg-stone-200">
                        @endif
                        <div class="mt-4 space-y-2 text-[11px]">
                            <p class="uppercase tracking-widest text-stone-400">Navn</p>
                            <p class="font-bold leading-tight">{{ $user->name }}</p>
                            @if ($user->job_title)
                                <p class="pt-2 uppercase tracking-widest text-stone-400">Stilling</p>
                                <p class="font-semibold leading-tight">{{ $user->job_title }}</p>
                            @endif
                            @if ($user->birthdate)
                                <p class="pt-2 uppercase tracking-widest text-stone-400">Født</p>
                                <p class="font-semibold tabular-nums">{{ $user->birthdate->format('d.m.Y') }}</p>
                            @endif
                            <p class="pt-2 uppercase tracking-widest text-stone-400">Udstedt</p>
                            <p class="font-semibold tabular-nums">{{ now()->format('d.m.Y') }}</p>
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-semibold text-stone-600 leading-relaxed">
                            {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                        </p>

                        @if (isset($coverLetter))
                            <section class="mt-5">
                                <h2 class="text-[11px] font-extrabold uppercase tracking-[0.3em] text-amber-700 border-b border-stone-300 pb-1.5">Ansøgning</h2>
                                <div class="mt-4 space-y-3.5 text-[14px] leading-relaxed text-stone-700">
                                    {!! $coverLetter->renderContext() !!}
                                </div>
                            </section>
                        @elseif ($jobs)
                            <section class="mt-5">
                                <h2 class="text-[11px] font-extrabold uppercase tracking-[0.3em] text-amber-700 border-b border-stone-300 pb-1.5">Erhvervserfaring &amp; uddannelse</h2>
                                <div class="mt-4 space-y-4">
                                    @foreach ($jobs as $job)
                                        <article class="border-l-2 border-amber-600 pl-4">
                                            <p class="text-[10px] font-bold tracking-widest text-stone-400 tabular-nums uppercase">
                                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                            </p>
                                            <h3 class="mt-0.5 font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                            <p class="text-[13px] font-medium text-stone-500">{{ $job['company'] }}</p>
                                            @if (!empty($job['description']))
                                                <p class="text-[13px] mt-1 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if ($skills)
                            <section class="mt-6">
                                <h2 class="text-[11px] font-extrabold uppercase tracking-[0.3em] text-amber-700 border-b border-stone-300 pb-1.5">Kompetencer</h2>
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach ($skills as $skill)
                                        <span class="text-[12px] font-semibold text-stone-700 border border-stone-400 bg-white px-3 py-1">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Maskinlæselig zone i bunden --}}
            <footer class="absolute bottom-0 inset-x-0 bg-[#f5f2e8] border-t border-stone-300 px-12 py-4">
                <p class="mrz text-stone-700">{{ $mrz1 }}</p>
                <p class="mrz text-stone-700">{{ $mrz2 }}</p>
            </footer>
        </div>
    </div>
</body>
</html>