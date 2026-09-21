{{-- Flank Right --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-stone-800 shadow-lg min-h-[297mm] font-sans grid grid-cols-[1fr_60mm]">

        {{-- Hovedkolonne --}}
        <main class="px-12 py-12 flex flex-col min-w-0">
            <header>
                <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-[#1f3a5f]">Curriculum Vitae</p>
                <h1 class="mt-3 font-serif text-5xl font-bold leading-tight text-stone-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg italic text-stone-500">{{ $user->job_title }}</p>
                @endif
            </header>

            <div class="mt-7 flex-1">
                @if (isset($coverLetter))
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#1f3a5f] border-b border-stone-200 pb-2.5">Ansøgning</h2>
                    <div class="mt-6 space-y-4">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                @elseif ($jobs)
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#1f3a5f] border-b border-stone-200 pb-2.5">Erhvervserfaring &amp; Uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article>
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-stone-400 tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-serif text-xl font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                <p class="text-sm text-[#1f3a5f]">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            <footer class="mt-10 border-t border-stone-200 pt-3 flex justify-between text-[10px] uppercase tracking-[0.3em] text-stone-400">
                <span>{{ $user->name }}</span>
                <span>Curriculum Vitae</span>
            </footer>
        </main>

        {{-- Blåtonet højre-sidebar --}}
        <aside class="bg-[#eaf0f6] border-l border-stone-200 px-7 py-12 flex flex-col gap-8">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-32 h-32 rounded-full object-cover mx-auto ring-1 ring-stone-300 ring-offset-4 ring-offset-[#eaf0f6]">
            @endif

            <section>
                <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-[#1f3a5f] border-b border-stone-300 pb-2">Kontakt</h2>
                <ul class="mt-3.5 space-y-2 text-xs text-stone-600 leading-relaxed">
                    @if ($user->phone)
                        <li>{{ $user->phone }}</li>
                    @endif
                    <li class="break-all">{{ $user->email }}</li>
                    @if ($user->address || $user->city)
                        <li>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</li>
                    @endif
                    @if ($user->birthdate)
                        <li>Født {{ $user->birthdate->format('d/m/Y') }}</li>
                    @endif
                </ul>
            </section>

            @if ($skills)
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-[#1f3a5f] border-b border-stone-300 pb-2">Kompetencer</h2>
                    <ul class="mt-3.5 space-y-2 text-xs text-stone-700">
                        @foreach ($skills as $skill)
                            <li class="flex items-start gap-2">
                                <span class="mt-[6px] w-1.5 h-1.5 bg-[#1f3a5f] shrink-0"></span>
                                <span>{{ $skill }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-[#1f3a5f] border-b border-stone-300 pb-2">Links</h2>
                    <ul class="mt-3.5 space-y-2.5 text-xs">
                        @foreach ($links as $link)
                            <li>
                                <p class="font-bold text-stone-800">{{ $link->name }}</p>
                                <a href="{{ $link->url }}" class="text-[#1f3a5f] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>
    </div>
</body>
</html>