{{-- Film Strip --}}
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
        /* Sproglhuller langs filmstrimmelens kanter */
        .sprockets {
            background-image: repeating-linear-gradient(
                to right,
                transparent 0,
                transparent 9px,
                #e5e7eb 9px,
                #e5e7eb 17px,
                transparent 17px,
                transparent 26px
            );
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-neutral-900 overflow-hidden">

        {{-- Filmstrimmel med foto i én brik --}}
        <header class="bg-neutral-900 text-white">
            <div class="h-5 sprockets opacity-80"></div>
            <div class="flex items-stretch px-10 py-4 gap-0">
                <div class="w-3 sprockets opacity-80"></div>
                <div class="flex-1 px-4">
                    <div class="flex items-center gap-7">
                        @if ($photo)
                            <div class="w-24 h-24 shrink-0 border-4 border-neutral-500 p-0.5 bg-black">
                                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover grayscale contrast-125">
                            </div>
                        @endif
                        <div class="font-mono">
                            <h1 class="text-3xl font-bold tracking-tight uppercase">{{ $user->name }}</h1>
                            @if ($user->job_title)
                                <p class="mt-1 text-sm text-amber-400 font-semibold tracking-widest uppercase">{{ $user->job_title }}</p>
                            @endif
                            <p class="mt-3 text-xs text-neutral-400 leading-relaxed">
                                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="w-3 sprockets opacity-80"></div>
            </div>
            <div class="h-5 sprockets opacity-80"></div>
        </header>

        <div class="px-14 py-10 font-sans">
            @if (isset($coverLetter))
                <section>
                    <h2 class="font-mono text-xs font-bold uppercase tracking-[0.3em] text-neutral-900 flex items-center gap-3">
                        <span class="text-amber-500">●</span> Ansøgning
                        <span class="flex-1 h-px bg-neutral-300"></span>
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-neutral-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="font-mono text-xs font-bold uppercase tracking-[0.3em] text-neutral-900 flex items-center gap-3">
                        <span class="text-amber-500">●</span> Erhvervserfaring &amp; uddannelse
                        <span class="flex-1 h-px bg-neutral-300"></span>
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5">
                                <div class="shrink-0 w-10 h-10 border-2 border-neutral-900 flex items-center justify-center font-mono text-xs font-bold">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('y') }}
                                </div>
                                <div class="flex-1 border-b border-dashed border-neutral-300 pb-5">
                                    <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                        <h3 class="font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                        <p class="font-mono text-xs font-bold text-neutral-400 tabular-nums whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                    </div>
                                    <p class="text-sm font-semibold text-amber-600">{{ $job['company'] }}</p>
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
                    <h2 class="font-mono text-xs font-bold uppercase tracking-[0.3em] text-neutral-900 flex items-center gap-3">
                        <span class="text-amber-500">●</span> Kompetencer
                        <span class="flex-1 h-px bg-neutral-300"></span>
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="font-mono text-[13px] font-semibold text-neutral-800 border-2 border-neutral-900 bg-amber-100/60 px-3 py-1">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-12 flex justify-between font-mono text-[10px] text-neutral-400 uppercase tracking-widest">
                <span>Reel 01 — {{ $user->name }}</span>
                <span>{{ now()->format('Y') }}</span>
            </footer>
        </div>
    </div>
</body>
</html>