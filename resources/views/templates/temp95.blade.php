{{-- Sage Panel --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#eef0eb] text-stone-800 shadow-lg min-h-[297mm] font-sans grid grid-cols-[62mm_1fr]">

        {{-- Dyb salie-panel --}}
        <aside class="bg-[#41493c] text-stone-100 px-7 py-12 flex flex-col gap-8">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-32 h-32 rounded-full object-cover mx-auto ring-4 ring-[#eef0eb]/90">
            @endif

            <section>
                <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-stone-300/80">Kontakt</h2>
                <ul class="mt-3 space-y-2 text-xs text-stone-100/85 leading-relaxed">
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
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-stone-300/80">Kompetencer</h2>
                    <ul class="mt-3 space-y-2 text-xs text-stone-100/85">
                        @foreach ($skills as $skill)
                            <li class="flex items-start gap-2">
                                <span class="mt-1.5 w-1.5 h-1.5 bg-[#b6c4a5] shrink-0"></span>
                                <span>{{ $skill }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section>
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.3em] text-stone-300/80">Links</h2>
                    <ul class="mt-3 space-y-2 text-xs text-stone-100/85">
                        @foreach ($links as $link)
                            <li>
                                <p class="font-bold text-stone-100">{{ $link->name }}</p>
                                <a href="{{ $link->url }}" class="text-[#b6c4a5] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>

        <main class="bg-white px-12 py-12 flex flex-col">
            <header>
                <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-[#6b7a5e]">Curriculum Vitae</p>
                <h1 class="mt-3 text-4xl font-bold tracking-tight text-stone-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg text-[#6b7a5e]">{{ $user->job_title }}</p>
                @endif
                <div class="mt-6 h-0.5 w-16 bg-[#6b7a5e]"></div>
            </header>

            @if (isset($coverLetter))
                <section class="mt-10 flex-1">
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-stone-900 border-b border-stone-200 pb-2.5">Ansøgning</h2>
                    <div class="mt-6 space-y-4">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-10 flex-1">
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-stone-900 border-b border-stone-200 pb-2.5">Erhvervserfaring &amp; Uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="relative pl-6 border-l-2 border-stone-200">
                                <span class="absolute -left-[7px] top-1.5 w-3 h-3 rounded-full bg-[#6b7a5e] ring-4 ring-white"></span>
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-stone-400">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 text-lg font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                <p class="text-sm text-[#6b7a5e]">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-10 border-t border-stone-200 pt-3 text-[10px] uppercase tracking-[0.3em] text-stone-400">
                {{ $user->name }} — Curriculum Vitae
            </footer>
        </main>
    </div>
</body>
</html>