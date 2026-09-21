{{-- Warm Terracotta --}}
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
        /* Varm terrakotta-spalte i stuetone */
        .cv-page { background: linear-gradient(180deg, #9a3412 0%, #7c2d12 60%, #9a3412 100%); }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto shadow-lg min-h-[297mm] font-sans text-orange-50 overflow-hidden grid grid-cols-[280px_1fr]">

        {{-- Venstre spalte: profil, kompetencer og links --}}
        <aside class="px-8 py-12 flex flex-col">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-28 h-28 rounded-full object-cover ring-2 ring-amber-200/70 mx-auto">
            @endif
            <p class="mt-6 text-center text-[10px] font-bold uppercase tracking-[0.4em] text-amber-200">Kontakt</p>
            <div class="mt-4 space-y-2.5 text-[13px] leading-relaxed text-orange-50/95 break-words">
                @if ($user->phone)<p>{{ $user->phone }}</p>@endif
                @if ($user->email)<p class="break-all">{{ $user->email }}</p>@endif
                @if ($user->address || $user->city)
                    <p>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                @endif
                @if ($user->birthdate)<p>Født {{ $user->birthdate->format('d/m/Y') }}</p>@endif
            </div>

            @if ($skills)
                <div class="mt-10">
                    <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-amber-200">Kompetencer</p>
                    <ul class="mt-4 space-y-2">
                        @foreach ($skills as $skill)
                            <li class="flex items-center gap-2.5 text-[13px] text-orange-50">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-300 shrink-0"></span>{{ $skill }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($links->isNotEmpty())
                <div class="mt-10">
                    <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-amber-200">Links</p>
                    <ul class="mt-4 space-y-2.5 text-[13px]">
                        @foreach ($links as $link)
                            <li class="break-all">
                                <a href="{{ $link->url }}" class="text-orange-50 underline decoration-amber-300/60 underline-offset-2 hover:decoration-amber-200">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>

        {{-- Højre spalte: hvidt papir med navn, erfaring og ansøgning --}}
        <main class="bg-white px-12 py-12 text-slate-700">
            <header>
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 leading-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg font-semibold text-orange-700">{{ $user->job_title }}</p>
                @endif
                <div class="mt-5 h-1 w-20 rounded-full bg-gradient-to-r from-orange-600 to-amber-400"></div>
            </header>

            @if (isset($coverLetter))
                <section class="mt-10">
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-900">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-600">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-10">
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-900">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="border-l-2 border-orange-200 pl-5">
                                <p class="text-[11px] font-bold tracking-widest text-orange-700 tabular-nums uppercase">
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

            <footer class="mt-12 pt-4 border-t border-slate-200 flex justify-between text-[10px] font-semibold uppercase tracking-[0.25em] text-slate-400">
                <span>{{ $user->name }}</span>
                <span>{{ now()->format('m.Y') }}</span>
            </footer>
        </main>
    </div>
</body>
</html>