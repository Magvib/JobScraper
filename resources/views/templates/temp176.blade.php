{{-- Meadow Green --}}
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
        /* Frisk eng over sidens top */
        .meadow {
            background:
                radial-gradient(ellipse 70% 55% at 15% 0%, rgba(134, 239, 172, .35), transparent 70%),
                radial-gradient(ellipse 55% 45% at 85% 0%, rgba(74, 222, 128, .25), transparent 70%),
                linear-gradient(180deg, #f0fdf4, #f0fdf4 30%, #ffffff 100%);
        }
        .leaf {
            width: 14px; height: 14px;
            border-radius: 9999px 9999px 9999px 2px;
            transform: rotate(-45deg);
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
    <div class="cv-page meadow max-w-[210mm] mx-auto shadow-lg min-h-[297mm] font-sans text-slate-700 overflow-hidden grid grid-cols-[240px_1fr]">

        {{-- Grøn sidebjælke med kontakt og kompetencer --}}
        <aside class="bg-emerald-900/95 px-7 py-11 flex flex-col text-emerald-50">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full object-cover ring-4 ring-emerald-300/40 mx-auto">
            @endif
            <p class="mt-7 text-[10px] font-bold uppercase tracking-[0.4em] text-emerald-300">Kontakt</p>
            <div class="mt-4 space-y-2.5 text-[13px] leading-relaxed text-emerald-50/95 break-words">
                @if ($user->phone)<p>{{ $user->phone }}</p>@endif
                @if ($user->email)<p class="break-all">{{ $user->email }}</p>@endif
                @if ($user->address || $user->city)
                    <p>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                @endif
                @if ($user->birthdate)<p>Født {{ $user->birthdate->format('d/m/Y') }}</p>@endif
            </div>

            @if ($skills)
                <div class="mt-9">
                    <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-emerald-300">Kompetencer</p>
                    <ul class="mt-4 space-y-2">
                        @foreach ($skills as $skill)
                            <li class="flex items-start gap-2.5 text-[13px] text-emerald-50">
                                <span class="leaf bg-emerald-400/80 mt-0.5 shrink-0" style="width:9px; height:9px;"></span>{{ $skill }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($links->isNotEmpty())
                <div class="mt-9">
                    <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-emerald-300">Links</p>
                    <ul class="mt-4 space-y-2.5 text-[13px]">
                        @foreach ($links as $link)
                            <li class="break-all">
                                <a href="{{ $link->url }}" class="text-emerald-50 underline decoration-emerald-400/60 underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>

        {{-- Let eng-hoved med bløde blade --}}
        <main class="px-10 py-11">
            <header>
                <div class="flex items-center gap-3">
                    <span class="leaf bg-emerald-500"></span>
                    <span class="leaf bg-emerald-400" style="width:10px;height:10px;"></span>
                    <span class="leaf bg-emerald-300" style="width:7px;height:7px;"></span>
                </div>
                <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-slate-900 leading-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-lg font-semibold text-emerald-700">{{ $user->job_title }}</p>
                @endif
            </header>

            @if (isset($coverLetter))
                <section class="mt-9">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-emerald-800 border-b-2 border-emerald-200 pb-2">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-600">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-9">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-emerald-800 border-b-2 border-emerald-200 pb-2">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="relative pl-7">
                                <span class="leaf bg-emerald-500 absolute left-0 top-1.5"></span>
                                <p class="text-[11px] font-bold tracking-widest text-emerald-600 tabular-nums uppercase">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 text-lg font-bold text-slate-900 leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm font-semibold text-slate-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-1.5 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-12 pt-4 border-t border-emerald-200 flex justify-between text-[10px] font-bold uppercase tracking-[0.3em] text-emerald-800/70">
                <span>{{ $user->name }}</span>
                <span>Vokser videre · {{ now()->format('m.Y') }}</span>
            </footer>
        </main>
    </div>
</body>
</html>