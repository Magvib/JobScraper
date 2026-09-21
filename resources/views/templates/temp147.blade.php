{{-- Chevron Cut --}}
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
        /* V-udskæring i bunden af headeren */
        .chevron {
            clip-path: polygon(0 0, 100% 0, 100% calc(100% - 34px), 50% 100%, 0 calc(100% - 34px));
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
    <div class="cv-page max-w-[210mm] mx-auto bg-slate-50 shadow-xl min-h-[297mm] font-sans text-slate-800 overflow-hidden">

        {{-- Header med hak i midten --}}
        <header class="chevron bg-cyan-900 text-white px-14 pt-14 pb-20">
            <div class="flex items-center gap-8">
                @if ($photo)
                    <div class="w-24 h-24 shrink-0 rounded-full bg-cyan-800 border border-cyan-500/50 p-1.5">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-full">
                    </div>
                @endif
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-lg font-medium text-cyan-300">{{ $user->job_title }}</p>
                    @endif
                </div>
            </div>
            <p class="mt-7 text-sm text-cyan-100">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </p>
        </header>

        <div class="px-14 pt-6 pb-12">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-cyan-900 flex items-center gap-3">
                        <span class="w-4 h-4 bg-cyan-900" style="clip-path: polygon(0 0, 100% 0, 50% 100%)"></span> Ansøgning
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-cyan-900 flex items-center gap-3">
                        <span class="w-4 h-4 bg-cyan-900" style="clip-path: polygon(0 0, 100% 0, 50% 100%)"></span> Erhvervserfaring &amp; uddannelse
                    </h2>
                    <div class="mt-6 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="bg-white rounded-lg shadow-sm border border-slate-200 px-7 py-5">
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold tracking-widest text-cyan-700 tabular-nums whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-medium text-slate-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-cyan-900 flex items-center gap-3">
                        <span class="w-4 h-4 bg-cyan-900" style="clip-path: polygon(0 0, 100% 0, 50% 100%)"></span> Kompetencer
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-cyan-900 bg-cyan-50 border border-cyan-200 px-3.5 py-1.5 rounded">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-9">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-cyan-900 flex items-center gap-3">
                        <span class="w-4 h-4 bg-cyan-900" style="clip-path: polygon(0 0, 100% 0, 50% 100%)"></span> Links
                    </h2>
                    <ul class="mt-5 text-sm space-y-1.5 text-slate-700">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold text-slate-900">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-cyan-700 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-12 pt-4 border-t border-slate-200 flex justify-between text-xs text-slate-400 tracking-wide">
                <span>{{ $user->name }}</span>
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>