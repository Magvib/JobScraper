{{-- Brick Press --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#f7f4ef] text-stone-800 shadow-lg min-h-[297mm] font-sans px-14 py-12 flex flex-col">

        {{-- Redaktionelt split-hoved --}}
        <header class="grid grid-cols-[1fr_64mm] gap-10 items-start pb-8 border-b-2 border-[#9a3412]">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-[#9a3412]">Curriculum Vitae</p>
                <h1 class="mt-3 font-serif text-5xl font-bold leading-[1.05] text-stone-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-3 font-serif text-lg italic text-stone-500">{{ $user->job_title }}</p>
                @endif
            </div>
            <div class="text-right">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 rounded-full object-cover border border-stone-400 ml-auto mb-4">
                @endif
                <ul class="space-y-1 text-xs text-stone-600">
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
            </div>
        </header>

        {{-- Erhvervserfaring --}}
        @if (isset($coverLetter))
            <section class="mt-9 flex-1">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.35em] text-[#9a3412]">Ansøgning</h2>
                <div class="mt-6 space-y-4">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-9 flex-1">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.35em] text-[#9a3412]">Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-6 divide-y divide-stone-300/70">
                    @foreach ($jobs as $job)
                        <article class="py-5 first:pt-0">
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#9a3412] tabular-nums">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                –
                                {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-1 font-serif text-xl font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                            <p class="text-sm text-stone-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Kompetencer i to kolonner --}}
        @if ($skills)
            <section class="mt-8">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.35em] text-[#9a3412]">Kompetencer</h2>
                <div class="mt-5 grid grid-cols-2 gap-x-10">
                    @foreach ($skills as $skill)
                        <p class="flex items-center gap-3 border-b border-stone-300/70 py-2 text-sm text-stone-700">
                            <span class="w-1.5 h-1.5 bg-[#9a3412] shrink-0"></span>
                            {{ $skill }}
                        </p>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Links --}}
        @if ($links->isNotEmpty())
            <section class="mt-8">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.35em] text-[#9a3412]">Links</h2>
                <ul class="mt-5 space-y-1.5 text-sm">
                    @foreach ($links as $link)
                        <li class="flex flex-wrap items-baseline gap-x-2">
                            <span class="font-semibold text-stone-900">{{ $link->name }}</span>
                            <a href="{{ $link->url }}" class="text-[#9a3412] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Footer --}}
        <footer class="mt-10 border-t border-stone-300 pt-3 flex justify-between text-[10px] uppercase tracking-[0.3em] text-stone-400">
            <span>{{ $user->name }}</span>
            <span>Curriculum Vitae</span>
        </footer>
    </div>
</body>
</html>