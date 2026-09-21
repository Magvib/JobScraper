{{-- Stitched Patch --}}
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
        /* Syet kanter: stiplet kant indersiden af "lappen" */
        .stitch {
            border: 1px dashed #78716c;
            outline-offset: -10px;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#f5efe4] shadow-xl min-h-[297mm] font-sans text-stone-800 p-8">

        {{-- Lappe-plakat med sykant --}}
        <div class="bg-[#eae0cd] min-h-[281mm] p-3">
            <div class="border border-dashed border-stone-400 h-full min-h-[267mm] px-12 py-12 relative">

                {{-- Syhjørner med lille kryds --}}
                <span class="absolute top-3 left-3 text-stone-400 font-mono text-xs">✕</span>
                <span class="absolute top-3 right-3 text-stone-400 font-mono text-xs">✕</span>
                <span class="absolute bottom-3 left-3 text-stone-400 font-mono text-xs">✕</span>
                <span class="absolute bottom-3 right-3 text-stone-400 font-mono text-xs">✕</span>

                <header class="flex items-center gap-8 pb-8 border-b-2 border-stone-500/50">
                    @if ($photo)
                        <div class="shrink-0 border-2 border-dashed border-stone-500 p-1.5">
                            <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover sepia-20">
                        </div>
                    @endif
                    <div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-stone-900">{{ $user->name }}</h1>
                        @if ($user->job_title)
                            <p class="mt-1.5 text-base font-semibold text-orange-800">{{ $user->job_title }}</p>
                        @endif
                        <p class="mt-4 text-sm text-stone-600">
                            {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                        </p>
                    </div>
                </header>

                @if (isset($coverLetter))
                    <section class="mt-8">
                        <h2 class="inline-block bg-orange-800 text-[#f5efe4] text-xs font-extrabold uppercase tracking-[0.25em] px-5 py-2 border-2 border-orange-900/30 -rotate-1">Ansøgning</h2>
                        <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-stone-700">
                            {!! $coverLetter->renderContext() !!}
                        </div>
                    </section>
                @elseif ($jobs)
                    <section class="mt-8">
                        <h2 class="inline-block bg-orange-800 text-[#f5efe4] text-xs font-extrabold uppercase tracking-[0.25em] px-5 py-2 border-2 border-orange-900/30 -rotate-1">Erhvervserfaring &amp; uddannelse</h2>
                        <div class="mt-7 space-y-6">
                            @foreach ($jobs as $job)
                                <article class="bg-[#f5efe4] border border-dashed border-stone-400 px-6 py-4">
                                    <p class="text-[11px] font-bold tracking-widest text-orange-800 tabular-nums uppercase">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <h3 class="mt-1 font-bold text-lg leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-stone-500">{{ $job['company'] }}</p>
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
                        <h2 class="inline-block bg-stone-700 text-[#f5efe4] text-xs font-extrabold uppercase tracking-[0.25em] px-5 py-2 border-2 border-stone-800/30 rotate-1">Kompetencer</h2>
                        <div class="mt-5 flex flex-wrap gap-2.5">
                            @foreach ($skills as $skill)
                                <span class="text-[13px] font-semibold text-stone-700 border border-dashed border-stone-500 bg-[#f5efe4] px-3.5 py-1.5">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($links->isNotEmpty())
                    <section class="mt-9">
                        <h2 class="inline-block bg-orange-800 text-[#f5efe4] text-xs font-extrabold uppercase tracking-[0.25em] px-5 py-2 border-2 border-orange-900/30 -rotate-1">Links</h2>
                        <ul class="mt-6 space-y-1.5 text-sm text-stone-700">
                            @foreach ($links as $link)
                                <li class="border border-dashed border-stone-400 bg-[#f5efe4] px-5 py-2">
                                    <span class="font-semibold text-stone-900">{{ $link->name }}:</span>
                                    <a href="{{ $link->url }}" class="text-orange-800 underline break-all">{{ $link->prettifyUrl() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <footer class="mt-12 text-center text-xs text-stone-500 tracking-wide uppercase">
                    {{-- Mærkat som tøjmærke --}}
                    <span class="inline-block border border-stone-500 px-3 py-1 text-[10px] tracking-[0.3em]">
                        @if ($user->birthdate)
                            Født {{ $user->birthdate->format('d/m/Y') }} ·
                        @endif
                        {{ $user->name }}
                    </span>
                </footer>
            </div>
        </div>
    </div>
</body>
</html>