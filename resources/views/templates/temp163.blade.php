{{-- Nameplate --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-stone-800 shadow-lg min-h-[297mm] font-sans px-14 pt-12 pb-12 flex flex-col">

        {{-- Grafit-navneplade med foto der rager ud over kanten --}}
        <header class="relative pr-40">
            <div class="bg-stone-900 text-white px-9 py-8">
                <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-stone-400">Curriculum Vitae</p>
                <h1 class="mt-2.5 font-serif text-5xl font-bold leading-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-base text-amber-300/90 tracking-wide">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="absolute right-0 top-1/2 -translate-y-1/2 w-32 h-32 rounded-full object-cover ring-4 ring-white shadow-lg">
            @endif
        </header>

        {{-- Kontakt-række --}}
        <div class="mt-6 border-y border-stone-200 py-3 flex flex-wrap gap-x-6 gap-y-1 text-xs text-stone-600">
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

        {{-- Indhold: erfaring i fuld bredde med hængende datoer --}}
        <div class="mt-9 flex-1 space-y-10">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900 border-b-2 border-stone-900 pb-2.5">Ansøgning</h2>
                    <div class="mt-6 space-y-4">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900 border-b-2 border-stone-900 pb-2.5">Erhvervserfaring &amp; Uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[30mm_1fr] gap-6">
                                <p class="text-xs font-bold text-stone-900 tabular-nums leading-snug pt-1">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    <span class="block font-medium text-stone-400">– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}</span>
                                </p>
                                <div class="border-l border-stone-200 pl-6">
                                    <h3 class="text-lg font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm text-amber-700">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Kompetencer i to kolonner --}}
            @if ($skills)
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900 border-b-2 border-stone-900 pb-2.5">Kompetencer</h2>
                    <ul class="mt-5 grid grid-cols-3 gap-x-8 gap-y-2.5 text-sm text-stone-700">
                        @foreach ($skills as $skill)
                            <li class="flex items-start gap-2.5">
                                <span class="mt-[7px] w-1.5 h-1.5 bg-amber-500 shrink-0"></span>
                                <span>{{ $skill }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Links --}}
            @if ($links->isNotEmpty())
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900 border-b-2 border-stone-900 pb-2.5">Links</h2>
                    <ul class="mt-5 flex flex-wrap gap-x-12 gap-y-3 text-sm">
                        @foreach ($links as $link)
                            <li>
                                <p class="font-bold text-stone-900">{{ $link->name }}</p>
                                <a href="{{ $link->url }}" class="text-amber-700 underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        {{-- Footer --}}
        <footer class="mt-12 border-t-2 border-stone-900 pt-3 flex justify-between text-[10px] uppercase tracking-[0.3em] text-stone-400">
            <span>{{ $user->name }}</span>
            <span>Curriculum Vitae</span>
        </footer>
    </div>
</body>
</html>