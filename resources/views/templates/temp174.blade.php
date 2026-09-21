{{-- Nordic Hygge --}}
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
        /* Blødt, hyggeligt papir i cremet tone */
        .paper {
            background:
                radial-gradient(ellipse 60% 30% at 100% 0%, rgba(251, 191, 36, .10), transparent 70%),
                radial-gradient(ellipse 50% 35% at 0% 100%, rgba(180, 83, 9, .07), transparent 70%),
                #faf5ec;
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
    <div class="cv-page paper max-w-[210mm] mx-auto shadow-lg min-h-[297mm] font-sans text-stone-700 overflow-hidden px-12 py-12">

        {{-- Hyggeligt hoved med rundt foto og bløde former --}}
        <header class="flex items-center gap-8">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                    class="w-28 h-28 rounded-[2rem] object-cover ring-4 ring-white shadow-md shrink-0">
            @endif
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-amber-600">Hyggelig hilsen fra</p>
                <h1 class="mt-2 text-4xl font-extrabold tracking-tight text-stone-800 leading-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-lg font-semibold text-amber-700">{{ $user->job_title }}</p>
                @endif
            </div>
        </header>

        {{-- Kontakt som bløde piller --}}
        <div class="mt-7 flex flex-wrap items-center gap-x-3 gap-y-2">
            @if ($user->phone)
                <span class="inline-flex items-center gap-1.5 bg-white/80 border border-amber-200/70 rounded-full px-3.5 py-1.5 text-[12px] font-semibold text-stone-600 shadow-sm">{{ $user->phone }}</span>
            @endif
            @if ($user->email)
                <span class="inline-flex items-center gap-1.5 bg-white/80 border border-amber-200/70 rounded-full px-3.5 py-1.5 text-[12px] font-semibold text-stone-600 shadow-sm break-all">{{ $user->email }}</span>
            @endif
            @if ($user->address || $user->city)
                <span class="inline-flex items-center gap-1.5 bg-white/80 border border-amber-200/70 rounded-full px-3.5 py-1.5 text-[12px] font-semibold text-stone-600 shadow-sm">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)
                <span class="inline-flex items-center gap-1.5 bg-white/80 border border-amber-200/70 rounded-full px-3.5 py-1.5 text-[12px] font-semibold text-stone-600 shadow-sm">Født {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </div>

        @if (isset($coverLetter))
            <section class="mt-10">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-stone-800 flex items-center gap-3">
                    <span class="w-8 h-[3px] rounded-full bg-amber-500"></span>Ansøgning
                </h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-600">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-10">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-stone-800 flex items-center gap-3">
                    <span class="w-8 h-[3px] rounded-full bg-amber-500"></span>Erhvervserfaring &amp; uddannelse
                </h2>
                <div class="mt-6 space-y-5">
                    @foreach ($jobs as $job)
                        <article class="bg-white/80 border border-amber-100 rounded-2xl px-6 py-5 shadow-sm">
                            <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                <h3 class="text-lg font-bold text-stone-800 leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-[11px] font-bold tracking-widest text-amber-600 tabular-nums uppercase whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="mt-0.5 text-sm font-semibold text-stone-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2.5 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-10">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-stone-800 flex items-center gap-3">
                    <span class="w-8 h-[3px] rounded-full bg-amber-500"></span>Kompetencer
                </h2>
                <div class="mt-5 flex flex-wrap gap-2.5">
                    @foreach ($skills as $skill)
                        <span class="text-[13px] font-semibold text-amber-800 bg-amber-100/80 border border-amber-200 rounded-full px-4 py-1.5">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-stone-800 flex items-center gap-3">
                    <span class="w-8 h-[3px] rounded-full bg-amber-500"></span>Links
                </h2>
                <ul class="mt-5 space-y-2 text-sm">
                    @foreach ($links as $link)
                        <li class="flex items-center gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                            <span class="font-semibold text-stone-800">{{ $link->name }}:</span>
                            <a href="{{ $link->url }}" class="text-stone-600 underline decoration-amber-300 underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-12 pt-5 border-t border-amber-200 flex justify-between text-[10px] font-bold uppercase tracking-[0.3em] text-stone-400">
            <span>{{ $user->name }}</span>
            <span>Med varme hilsner · {{ now()->format('m.Y') }}</span>
        </footer>
    </div>
</body>
</html>