{{-- Poster Cut --}}
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

        {{-- Plakat-hoved: navn i gigantisk typografi + skrå accent --}}
        <header class="relative px-14 pt-12 pb-9 overflow-hidden border-b-2 border-stone-900">
            <div class="absolute top-0 right-0 w-40 h-full bg-[#e25822]/10" style="clip-path: polygon(45% 0, 100% 0, 100% 100%, 25% 100%);"></div>
            <div class="absolute top-0 right-0 w-24 h-full bg-[#e25822]/25" style="clip-path: polygon(70% 0, 100% 0, 100% 100%, 50% 100%);"></div>
            <div class="relative flex items-end justify-between gap-8">
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.5em] text-[#e25822]">Curriculum Vitae</p>
                    <h1 class="mt-3 font-serif text-[17mm] font-bold leading-[0.9] tracking-tight text-stone-900 break-words">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-4 text-lg text-stone-500">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <div class="relative shrink-0 mb-1">
                        <span class="absolute -right-2 -top-2 w-full h-full bg-[#e25822]"></span>
                        <img src="{{ $photo }}" alt="{{ $user->name }}"
                             class="relative w-28 h-32 object-cover border border-stone-900">
                    </div>
                @endif
            </div>
        </header>

        {{-- Kontakt-række --}}
        <div class="px-14 py-3.5 bg-stone-100 flex flex-wrap gap-x-6 gap-y-1 text-xs text-stone-600">
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

        {{-- Indhold i to kolonner --}}
        <div class="px-14 py-10 grid grid-cols-[1fr_50mm] gap-12 flex-1">
            {{-- Erhvervserfaring --}}
            <main>
                @if (isset($coverLetter))
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#e25822] flex items-center gap-3">
                        Ansøgning
                        <span class="flex-1 h-px bg-stone-200"></span>
                    </h2>
                    <div class="mt-6 space-y-4">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                @elseif ($jobs)
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#e25822] flex items-center gap-3">
                        Erhvervserfaring &amp; Uddannelse
                        <span class="flex-1 h-px bg-stone-200"></span>
                    </h2>
                    <div class="mt-6 space-y-7">
                        @foreach ($jobs as $job)
                            <article>
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-stone-400 tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-serif text-xl font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                <p class="text-sm text-[#e25822]">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </main>

            {{-- Kompetencer & links --}}
            @if ($skills || $links->isNotEmpty())
                <aside>
                    @if ($skills)
                        <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#e25822] flex items-center gap-3">
                            Kompetencer
                        </h2>
                        <ul class="mt-5 space-y-2.5 text-sm text-stone-700">
                            @foreach ($skills as $skill)
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-[7px] w-1.5 h-1.5 bg-[#e25822] shrink-0"></span>
                                    <span>{{ $skill }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($links->isNotEmpty())
                        <h2 class="mt-8 text-[10px] font-bold uppercase tracking-[0.35em] text-[#e25822]">Links</h2>
                        <ul class="mt-5 space-y-3 text-sm">
                            @foreach ($links as $link)
                                <li>
                                    <p class="font-bold text-stone-900">{{ $link->name }}</p>
                                    <a href="{{ $link->url }}" class="text-[#e25822] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </aside>
            @endif
        </div>

        {{-- Kraftig sort bundlinje --}}
        <footer class="bg-stone-900 text-stone-400 px-14 py-3.5 flex justify-between text-[10px] uppercase tracking-[0.3em]">
            <span>{{ $user->name }}</span>
            <span>Curriculum Vitae</span>
        </footer>
    </div>
</body>
</html>