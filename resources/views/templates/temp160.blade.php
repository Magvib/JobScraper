{{-- Dot Matrix --}}
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
        /* Punktraster-baggrund som rå printer-papir */
        .dotgrid {
            background-image: radial-gradient(circle, rgba(15,118,110,.12) 1px, transparent 1px);
            background-size: 10px 10px;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-mono text-teal-950 overflow-hidden">

        {{-- Punktraster-header med stor typografisk blok --}}
        <header class="dotgrid bg-teal-50 px-14 pt-14 pb-10 border-b-2 border-teal-800">
            <div class="flex items-center gap-8">
                @if ($photo)
                    <div class="shrink-0 border-2 border-teal-800 bg-white p-1.5 shadow-[4px_4px_0_#134e4a]">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover grayscale-30">
                    </div>
                @endif
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-teal-600">cv.txt · {{ now()->format('Y-m-d') }}</p>
                    <h1 class="mt-2 text-4xl font-black uppercase tracking-tight text-teal-950">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-sm font-bold uppercase tracking-widest text-teal-700">{{ $user->job_title }}</p>
                    @endif
                </div>
            </div>
            <p class="mt-6 text-xs text-teal-800">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  //  ') }}
            </p>
        </header>

        <div class="px-14 py-9">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-xs font-black uppercase tracking-[0.35em] text-teal-800 bg-teal-100 border border-teal-800 px-3 py-1.5 inline-block shadow-[3px_3px_0_#134e4a]">Ansøgning</h2>
                    <div class="mt-6 space-y-4 text-[14px] leading-relaxed text-teal-900">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-xs font-black uppercase tracking-[0.35em] text-teal-800 bg-teal-100 border border-teal-800 px-3 py-1.5 inline-block shadow-[3px_3px_0_#134e4a]">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="border border-teal-200 bg-white px-5 py-4">
                                <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                    <h3 class="font-bold text-base uppercase tracking-wide text-teal-950">&gt; {{ $job['title'] }}</h3>
                                    <p class="text-[11px] font-bold text-teal-600 tabular-nums whitespace-nowrap">
                                        [{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}]
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-teal-700">@ {{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-teal-800/80">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-8">
                    <h2 class="text-xs font-black uppercase tracking-[0.35em] text-teal-800 bg-teal-100 border border-teal-800 px-3 py-1.5 inline-block shadow-[3px_3px_0_#134e4a]">Kompetencer</h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-bold text-teal-900 border border-teal-800 bg-white px-3 py-1.5 shadow-[2px_2px_0_#134e4a]">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-10 pt-4 border-t border-dashed border-teal-300 flex justify-between text-[10px] uppercase tracking-[0.25em] text-teal-500">
                <span>{{ $user->name }}</span>
                <span>EOF · {{ now()->format('H:i') }}</span>
            </footer>
        </div>
    </div>
</body>
</html>