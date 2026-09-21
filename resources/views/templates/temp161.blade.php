{{-- Margin Note --}}
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
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-stone-800 shadow-lg min-h-[297mm] font-sans px-14 py-12 flex flex-col border-t-4 border-stone-800">

        {{-- Hoved med portræt til højre --}}
        <header class="flex items-start justify-between gap-10 pb-8 border-b border-stone-300">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-stone-500">Curriculum Vitae</p>
                <h1 class="mt-3 font-serif text-5xl font-bold leading-tight text-stone-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg text-stone-500 tracking-wide">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 rounded-full object-cover ring-1 ring-stone-300 ring-offset-4 shrink-0 mt-2">
            @endif
        </header>

        <div class="mt-5 flex flex-wrap gap-x-6 gap-y-1 text-xs text-stone-600">
            @if ($user->phone)
                <span><span class="font-bold text-stone-900">Tlf</span> {{ $user->phone }}</span>
            @endif
            <span class="break-all"><span class="font-bold text-stone-900">Mail</span> {{ $user->email }}</span>
            @if ($user->address || $user->city)
                <span><span class="font-bold text-stone-900">Adresse</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)
                <span><span class="font-bold text-stone-900">Født</span> {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </div>

        {{-- Indhold med datokolonne i venstre margen --}}
        <div class="mt-9 flex-1 space-y-9">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900 border-b border-stone-300 pb-2.5">Ansøgning</h2>
                    <div class="mt-6 grid grid-cols-[28mm_1fr] gap-6">
                        <span></span>
                        <div class="border-l border-stone-300 pl-6 space-y-4">
                            {!! $coverLetter->renderContext() !!}
                        </div>
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900 border-b border-stone-300 pb-2.5">Erhvervserfaring &amp; Uddannelse</h2>
                    <div class="mt-6 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[28mm_1fr] gap-6">
                                <div class="text-right">
                                    <p class="text-[11px] font-bold text-stone-900 tabular-nums leading-snug">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    </p>
                                    <p class="text-[10px] text-stone-400 tabular-nums">
                                        – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <div class="border-l border-stone-300 pl-6">
                                    <h3 class="text-lg font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm text-stone-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900 border-b border-stone-300 pb-2.5">Kompetencer</h2>
                    <div class="mt-5 grid grid-cols-[28mm_1fr] gap-6">
                        <span></span>
                        <ul class="border-l border-stone-300 pl-6 grid grid-cols-2 gap-x-8 gap-y-2 text-sm text-stone-700">
                            @foreach ($skills as $skill)
                                <li class="flex items-center gap-2.5">
                                    <span class="w-1.5 h-1.5 bg-stone-800 shrink-0"></span>
                                    {{ $skill }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900 border-b border-stone-300 pb-2.5">Links</h2>
                    <div class="mt-5 grid grid-cols-[28mm_1fr] gap-6">
                        <span></span>
                        <ul class="border-l border-stone-300 pl-6 space-y-2.5 text-sm">
                            @foreach ($links as $link)
                                <li class="flex flex-wrap items-baseline gap-x-2">
                                    <span class="font-bold text-stone-900">{{ $link->name }}</span>
                                    <a href="{{ $link->url }}" class="text-stone-600 underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif
        </div>

        {{-- Footer --}}
        <footer class="mt-10 pt-4 border-t border-stone-300 flex justify-between text-[10px] uppercase tracking-[0.3em] text-stone-400">
            <span>{{ $user->name }}</span>
            <span>Curriculum Vitae</span>
        </footer>
    </div>
</body>
</html>