{{-- Grid Spec --}}
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

        {{-- Hoved med foto i kantet ramme --}}
        <header class="flex items-end justify-between gap-10 pb-8 border-b-4 border-stone-900">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-amber-600">Curriculum Vitae</p>
                <h1 class="mt-3 text-5xl font-black uppercase tracking-tight leading-none text-stone-900 break-words">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-3 text-lg text-stone-500">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <div class="shrink-0 bg-amber-500 p-1.5">
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-28 h-28 object-cover">
                </div>
            @endif
        </header>

        {{-- Kontakt som spec-ark: fire felter med labels --}}
        <div class="mt-6 grid grid-cols-4 gap-6">
            <div>
                <p class="text-[9px] font-bold uppercase tracking-[0.25em] text-amber-600">Tlf</p>
                <p class="mt-1 text-sm font-semibold text-stone-900">{{ $user->phone ?? '—' }}</p>
            </div>
            <div class="min-w-0">
                <p class="text-[9px] font-bold uppercase tracking-[0.25em] text-amber-600">Mail</p>
                <p class="mt-1 text-sm font-semibold text-stone-900 break-all">{{ $user->email }}</p>
            </div>
            <div class="min-w-0">
                <p class="text-[9px] font-bold uppercase tracking-[0.25em] text-amber-600">Adresse</p>
                <p class="mt-1 text-sm font-semibold text-stone-900">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') ?: '—' }}</p>
            </div>
            <div>
                <p class="text-[9px] font-bold uppercase tracking-[0.25em] text-amber-600">Født</p>
                <p class="mt-1 text-sm font-semibold text-stone-900">{{ $user->birthdate ? $user->birthdate->format('d/m/Y') : '—' }}</p>
            </div>
        </div>

        {{-- Erhvervserfaring som kort i to kolonner --}}
        @if (isset($coverLetter))
            <section class="mt-10 flex-1">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-amber-600 border-b border-stone-200 pb-2.5">Ansøgning</h2>
                <div class="mt-6 space-y-4">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-10 flex-1">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-amber-600 border-b border-stone-200 pb-2.5">Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-6 grid grid-cols-2 gap-5">
                    @foreach ($jobs as $job)
                        <article class="border-t-[3px] border-t-amber-500 border-x border-b border-stone-200 px-6 py-5">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-stone-400 tabular-nums">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                –
                                {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-1 font-bold text-lg leading-snug text-stone-900">{{ $job['title'] }}</h3>
                            <p class="text-sm text-amber-700">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Kompetencer --}}
        @if ($skills)
            <section class="mt-10">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-amber-600 border-b border-stone-200 pb-2.5">Kompetencer</h2>
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
            <section class="mt-9">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-amber-600 border-b border-stone-200 pb-2.5">Links</h2>
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

        {{-- Footer --}}
        <footer class="mt-12 pt-3 border-t-4 border-stone-900 flex justify-between text-[10px] uppercase tracking-[0.3em] text-stone-400">
            <span>{{ $user->name }}</span>
            <span>Curriculum Vitae</span>
        </footer>
    </div>
</body>
</html>