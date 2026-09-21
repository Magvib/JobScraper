{{-- North Line --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-zinc-800 shadow-lg min-h-[297mm] font-sans px-14 pt-10 pb-12 flex flex-col">

        {{-- Dobbelt hairline øverst --}}
        <div class="border-t-2 border-zinc-900"></div>
        <div class="mt-[3px] border-t border-zinc-300"></div>

        {{-- Luftig header --}}
        <header class="mt-8 flex items-center justify-between gap-10">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.45em] text-[#5b7186]">Curriculum Vitae</p>
                <h1 class="mt-3 font-serif text-5xl font-medium leading-tight text-zinc-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg text-zinc-500 tracking-wide">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 rounded-full object-cover ring-1 ring-zinc-300 ring-offset-4 shrink-0">
            @endif
        </header>

        {{-- Kontakt-række --}}
        <div class="mt-7 border-y border-zinc-200 py-3 flex flex-wrap gap-x-6 gap-y-1 text-xs text-zinc-600">
            @if ($user->phone)
                <span><span class="font-semibold text-zinc-900">Tlf</span> {{ $user->phone }}</span>
            @endif
            <span class="break-all"><span class="font-semibold text-zinc-900">Mail</span> {{ $user->email }}</span>
            @if ($user->address || $user->city)
                <span><span class="font-semibold text-zinc-900">Adresse</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)
                <span><span class="font-semibold text-zinc-900">Født</span> {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </div>

        <div class="mt-9 grid grid-cols-[1fr_50mm] gap-12 flex-1">
            {{-- Erhvervserfaring på let tidslinje --}}
            <main>
                @if (isset($coverLetter))
                    <h2 class="text-[10px] font-semibold uppercase tracking-[0.35em] text-[#5b7186] border-b border-zinc-200 pb-2.5">Ansøgning</h2>
                    <div class="mt-6 space-y-4">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                @elseif ($jobs)
                    <h2 class="text-[10px] font-semibold uppercase tracking-[0.35em] text-[#5b7186] border-b border-zinc-200 pb-2.5">Erhvervserfaring &amp; Uddannelse</h2>
                    <div class="mt-7 relative pl-7">
                        <span class="absolute left-1 top-1 bottom-1 w-px bg-zinc-200"></span>
                        <div class="space-y-7">
                            @foreach ($jobs as $job)
                                <article class="relative">
                                    <span class="absolute -left-[26.5px] top-1.5 w-[7px] h-[7px] rounded-full bg-zinc-900 ring-4 ring-white"></span>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-zinc-400 tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <h3 class="mt-1 font-serif text-xl font-semibold leading-snug text-zinc-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm text-[#5b7186]">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="mt-2 text-sm leading-relaxed text-zinc-600">{{ $job['description'] }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            </main>

            {{-- Kompetencer & links --}}
            @if ($skills || $links->isNotEmpty())
                <aside>
                    @if ($skills)
                        <h2 class="text-[10px] font-semibold uppercase tracking-[0.35em] text-[#5b7186] border-b border-zinc-200 pb-2.5">Kompetencer</h2>
                        <ul class="mt-5 space-y-2.5 text-sm text-zinc-700">
                            @foreach ($skills as $skill)
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-[7px] w-1.5 h-1.5 bg-zinc-400 shrink-0"></span>
                                    <span>{{ $skill }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($links->isNotEmpty())
                        <h2 class="mt-8 text-[10px] font-semibold uppercase tracking-[0.35em] text-[#5b7186] border-b border-zinc-200 pb-2.5">Links</h2>
                        <ul class="mt-5 space-y-3 text-sm">
                            @foreach ($links as $link)
                                <li>
                                    <p class="font-semibold text-zinc-900">{{ $link->name }}</p>
                                    <a href="{{ $link->url }}" class="text-[#5b7186] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </aside>
            @endif
        </div>

        {{-- Afsluttende hairlines --}}
        <footer class="mt-10">
            <div class="border-t border-zinc-300"></div>
            <div class="mt-[3px] border-t-2 border-zinc-900"></div>
            <div class="mt-3 flex justify-between text-[10px] uppercase tracking-[0.3em] text-zinc-400">
                <span>{{ $user->name }}</span>
                <span>Curriculum Vitae</span>
            </div>
        </footer>
    </div>
</body>
</html>