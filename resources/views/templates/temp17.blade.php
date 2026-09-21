{{-- Studio Archive --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#f6f3ec] text-stone-800 shadow-lg min-h-[297mm] font-sans border-t-4 border-emerald-900 px-14 py-12 flex flex-col">

        {{-- Arkiv-hoved med almindeligt portræt --}}
        <header class="flex items-start justify-between gap-10">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-emerald-900">Curriculum Vitae</p>
                <h1 class="mt-3 font-serif text-5xl font-bold leading-tight text-stone-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 font-serif text-lg italic text-emerald-900">{{ $user->job_title }}</p>
                @endif
                <div class="mt-5 grid grid-cols-2 gap-x-6 gap-y-1.5 text-xs text-stone-600">
                    @if ($user->phone)
                        <p><span class="font-bold text-stone-900">Tlf</span> {{ $user->phone }}</p>
                    @endif
                    <p class="break-all"><span class="font-bold text-stone-900">Mail</span> {{ $user->email }}</p>
                    @if ($user->address || $user->city)
                        <p class="col-span-2"><span class="font-bold text-stone-900">Adresse</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                    @endif
                    @if ($user->birthdate)
                        <p><span class="font-bold text-stone-900">Født</span> {{ $user->birthdate->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>
            @if ($photo)
                <div class="relative shrink-0 mt-2">
                    <span class="absolute inset-0 translate-x-2 translate-y-2 border border-emerald-900/50"></span>
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="relative w-32 aspect-[3/4] object-cover border border-stone-400/70 bg-stone-200">
                </div>
            @endif
        </header>

        {{-- Nummererede arkiv-sektioner --}}
        <div class="mt-10 grid grid-cols-[1fr_48mm] gap-10 flex-1">
            <main>
                @if (isset($coverLetter))
                    <div class="flex items-baseline gap-3 border-b border-stone-300 pb-2">
                        <span class="text-xs font-bold text-emerald-900">01</span>
                        <h2 class="font-serif text-lg font-bold uppercase tracking-wide text-stone-900">Ansøgning</h2>
                    </div>
                    <div class="mt-6 space-y-4">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                @elseif ($jobs)
                    <div class="flex items-baseline gap-3 border-b border-stone-300 pb-2">
                        <span class="text-xs font-bold text-emerald-900">01</span>
                        <h2 class="font-serif text-lg font-bold uppercase tracking-wide text-stone-900">Erhvervserfaring &amp; Uddannelse</h2>
                    </div>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="border-l-2 border-emerald-900/25 pl-5">
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-900">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-serif text-lg font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                <p class="text-sm text-stone-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </main>

            @if ($skills || $links->isNotEmpty())
                <aside>
                    @if ($skills)
                        <div class="flex items-baseline gap-3 border-b border-stone-300 pb-2">
                            <span class="text-xs font-bold text-emerald-900">02</span>
                            <h2 class="font-serif text-lg font-bold uppercase tracking-wide text-stone-900">Kompetencer</h2>
                        </div>
                        <ul class="mt-5 space-y-2.5 text-sm">
                            @foreach ($skills as $skill)
                                <li class="flex items-start gap-2.5 border-b border-dotted border-stone-300 pb-2.5">
                                    <span class="mt-1.5 w-1.5 h-1.5 bg-emerald-900 shrink-0"></span>
                                    <span class="text-stone-700">{{ $skill }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($links->isNotEmpty())
                        <div class="mt-8 flex items-baseline gap-3 border-b border-stone-300 pb-2">
                            <span class="text-xs font-bold text-emerald-900">03</span>
                            <h2 class="font-serif text-lg font-bold uppercase tracking-wide text-stone-900">Links</h2>
                        </div>
                        <ul class="mt-5 space-y-3 text-sm">
                            @foreach ($links as $link)
                                <li>
                                    <p class="font-bold text-stone-800">{{ $link->name }}</p>
                                    <a href="{{ $link->url }}" class="text-emerald-900 underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </aside>
            @endif
        </div>

        {{-- Diskret footer --}}
        <footer class="mt-10 border-t border-stone-300 pt-3 flex justify-between text-[10px] uppercase tracking-[0.3em] text-stone-500">
            <span>{{ $user->name }}</span>
            <span>Arkiv</span>
        </footer>
    </div>
</body>
</html>