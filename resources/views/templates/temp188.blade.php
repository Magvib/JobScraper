{{-- Spine Label --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-stone-800 shadow-lg min-h-[297mm] font-sans grid grid-cols-[14mm_1fr]">

        {{-- Bogryg-søjle med roteret label --}}
        <div class="bg-stone-900 text-stone-400 relative flex flex-col items-center justify-between py-8">
            <span class="w-2.5 h-2.5 bg-amber-500 mt-2"></span>
            <p class="[writing-mode:vertical-rl] rotate-180 text-[10px] font-bold uppercase tracking-[0.5em] whitespace-nowrap select-none">
                Curriculum Vitae
            </p>
            <span class="w-2.5 h-2.5 bg-amber-500 mb-2"></span>
        </div>

        {{-- Indhold --}}
        <div class="px-12 py-12 flex flex-col min-w-0">
            <header class="flex items-start justify-between gap-10 pb-7 border-b border-stone-200">
                <div>
                    <h1 class="font-serif text-5xl font-bold leading-tight text-stone-900">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-lg text-amber-700 tracking-wide">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 object-cover border border-stone-300 shrink-0">
                @endif
            </header>

            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-1 text-xs text-stone-600">
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

            <div class="mt-8 grid grid-cols-[1fr_48mm] gap-12 flex-1">
                {{-- Erhvervserfaring --}}
                <main>
                    @if (isset($coverLetter))
                        <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900 border-b border-stone-200 pb-2.5">Ansøgning</h2>
                        <div class="mt-6 space-y-4">
                            {!! $coverLetter->renderContext() !!}
                        </div>
                    @elseif ($jobs)
                        <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900 border-b border-stone-200 pb-2.5">Erhvervserfaring &amp; Uddannelse</h2>
                        <div class="mt-6 space-y-6">
                            @foreach ($jobs as $job)
                                <article class="relative pl-5 border-l-2 border-amber-500">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-stone-400 tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <h3 class="mt-1 font-serif text-xl font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm text-amber-700">{{ $job['company'] }}</p>
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
                            <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900 border-b border-stone-200 pb-2.5">Kompetencer</h2>
                            <ul class="mt-5 space-y-2.5 text-sm text-stone-700">
                                @foreach ($skills as $skill)
                                    <li class="flex items-start gap-2.5">
                                        <span class="mt-[7px] w-1.5 h-1.5 bg-amber-500 shrink-0"></span>
                                        <span>{{ $skill }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($links->isNotEmpty())
                            <h2 class="mt-8 text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900 border-b border-stone-200 pb-2.5">Links</h2>
                            <ul class="mt-5 space-y-3 text-sm">
                                @foreach ($links as $link)
                                    <li>
                                        <p class="font-bold text-stone-900">{{ $link->name }}</p>
                                        <a href="{{ $link->url }}" class="text-amber-700 underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </aside>
                @endif
            </div>

            {{-- Footer --}}
            <footer class="mt-10 border-t border-stone-200 pt-3 flex justify-between text-[10px] uppercase tracking-[0.3em] text-stone-400">
                <span>{{ $user->name }}</span>
                <span>Curriculum Vitae</span>
            </footer>
        </div>
    </div>
</body>
</html>