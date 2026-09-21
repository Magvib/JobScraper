{{-- Axis --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-stone-800 shadow-lg min-h-[297mm] font-sans px-14 py-12 flex flex-col">

        <header class="flex items-start justify-between gap-10">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-[#b45309]">Curriculum Vitae</p>
                <h1 class="mt-3 font-serif text-5xl font-bold leading-tight text-stone-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg text-stone-500">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 rounded-full object-cover ring-1 ring-stone-300 ring-offset-4 shrink-0 mt-1">
            @endif
        </header>

        {{-- Kontakt-række --}}
        <div class="mt-7 border-y border-stone-200 py-3 flex flex-wrap gap-x-6 gap-y-1 text-xs text-stone-600">
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

        {{-- Central tidslinje: erhverv vekslende omkring midterlinjen --}}
        <div class="mt-10 flex-1">
            @if (isset($coverLetter))
                <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#b45309] border-b border-stone-200 pb-2.5">Ansøgning</h2>
                <div class="mt-6 space-y-4">
                    {!! $coverLetter->renderContext() !!}
                </div>
            @elseif ($jobs)
                <h2 class="text-center text-[10px] font-bold uppercase tracking-[0.35em] text-[#b45309]">Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-7 relative">
                    <span class="absolute left-1/2 top-1 bottom-1 w-px bg-stone-200"></span>
                    <div class="space-y-8">
                        @foreach ($jobs as $job)
                            <article class="relative grid grid-cols-2 gap-x-16">
                                <span class="absolute left-1/2 -translate-x-1/2 top-2 w-2.5 h-2.5 rounded-full bg-[#b45309] ring-4 ring-white"></span>
                                <div class="{{ $loop->odd ? 'text-right' : 'col-start-2 text-left' }}">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-stone-400 tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <h3 class="mt-1 font-serif text-xl font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm text-[#b45309]">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Kompetencer & links --}}
        <div class="mt-10 grid grid-cols-2 gap-12">
            @if ($skills)
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#b45309] border-b border-stone-200 pb-2.5">Kompetencer</h2>
                    <ul class="mt-5 grid grid-cols-2 gap-x-6 gap-y-2.5 text-sm text-stone-700">
                        @foreach ($skills as $skill)
                            <li class="flex items-start gap-2.5">
                                <span class="mt-[7px] w-1.5 h-1.5 bg-[#b45309] shrink-0"></span>
                                <span>{{ $skill }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#b45309] border-b border-stone-200 pb-2.5">Links</h2>
                    <ul class="mt-5 space-y-3 text-sm">
                        @foreach ($links as $link)
                            <li>
                                <p class="font-bold text-stone-900">{{ $link->name }}</p>
                                <a href="{{ $link->url }}" class="text-[#b45309] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        {{-- Footer --}}
        <footer class="mt-12 border-t border-stone-200 pt-3 flex justify-between text-[10px] uppercase tracking-[0.3em] text-stone-400">
            <span>{{ $user->name }}</span>
            <span>Curriculum Vitae</span>
        </footer>
    </div>
</body>
</html>