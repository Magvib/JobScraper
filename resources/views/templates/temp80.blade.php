{{-- Harbor --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-stone-800 shadow-lg min-h-[297mm] font-sans flex flex-col">

        {{-- Kompakt petrolblåt bånd med navn og kontakt --}}
        <header class="bg-[#1f4e5f] text-white px-14 py-10 flex items-center justify-between gap-10">
            <div class="min-w-0">
                <h1 class="font-serif text-5xl font-bold leading-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg text-white/80">{{ $user->job_title }}</p>
                @endif
                <div class="mt-5 flex flex-wrap gap-x-6 gap-y-1 text-xs text-white/75">
                    @if ($user->phone)
                        <span><span class="font-bold text-white">Tlf</span> {{ $user->phone }}</span>
                    @endif
                    <span class="break-all"><span class="font-bold text-white">Mail</span> {{ $user->email }}</span>
                    @if ($user->address || $user->city)
                        <span><span class="font-bold text-white">Adresse</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                    @endif
                    @if ($user->birthdate)
                        <span><span class="font-bold text-white">Født</span> {{ $user->birthdate->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 rounded-full object-cover ring-2 ring-white/80 shrink-0">
            @endif
        </header>

        {{-- Kobber accentstribe --}}
        <div class="h-1.5 bg-[#b0673f]"></div>

        <div class="px-14 py-10 flex-1">
            {{-- Erhvervserfaring --}}
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#b0673f] border-b border-stone-200 pb-2.5">Ansøgning</h2>
                    <div class="mt-6 space-y-4">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#b0673f] border-b border-stone-200 pb-2.5">Erhvervserfaring &amp; Uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-6 items-start">
                                <div class="shrink-0 w-[26mm] text-right border-r-2 border-[#1f4e5f]/30 pr-5 pt-0.5">
                                    <p class="text-sm font-bold text-[#1f4e5f] tabular-nums leading-tight">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}</p>
                                    <p class="text-[10px] text-stone-400 tabular-nums">– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}</p>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-serif text-xl font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm text-[#b0673f]">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Kompetencer --}}
            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#b0673f] border-b border-stone-200 pb-2.5">Kompetencer</h2>
                    <ul class="mt-5 grid grid-cols-3 gap-x-8 gap-y-2.5 text-sm text-stone-700">
                        @foreach ($skills as $skill)
                            <li class="flex items-start gap-2.5">
                                <span class="mt-[7px] w-1.5 h-1.5 bg-[#1f4e5f] shrink-0"></span>
                                <span>{{ $skill }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Links --}}
            @if ($links->isNotEmpty())
                <section class="mt-9">
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#b0673f] border-b border-stone-200 pb-2.5">Links</h2>
                    <ul class="mt-5 flex flex-wrap gap-x-12 gap-y-3 text-sm">
                        @foreach ($links as $link)
                            <li>
                                <p class="font-bold text-stone-900">{{ $link->name }}</p>
                                <a href="{{ $link->url }}" class="text-[#1f4e5f] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        {{-- Footer --}}
        <footer class="border-t border-stone-200 px-14 py-3 flex justify-between text-[10px] uppercase tracking-[0.3em] text-stone-400">
            <span>{{ $user->name }}</span>
            <span>Curriculum Vitae</span>
        </footer>
    </div>
</body>
</html>