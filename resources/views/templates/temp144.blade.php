{{-- Quoted Letter --}}
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
        /* Kæmpe citationstegn bag indholdet */
        .ghost-quote {
            position: absolute;
            top: 34mm;
            left: 8mm;
            font-family: Georgia, serif;
            font-size: 210px;
            line-height: 1;
            color: rgba(148, 163, 184, 0.18);
            pointer-events: none;
            user-select: none;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page relative max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-serif text-slate-900 overflow-hidden">

        <span class="ghost-quote">&ldquo;</span>

        {{-- Dyb blå band med centreret navn --}}
        <header class="bg-slate-900 text-white px-14 py-12 text-center">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover rounded-full mx-auto border-2 border-slate-400 p-1">
            @endif
            <h1 class="mt-4 text-4xl font-bold tracking-wide">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 text-base italic text-slate-300">{{ $user->job_title }}</p>
            @endif
            <p class="mt-4 font-sans text-xs uppercase tracking-[0.25em] text-slate-400">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </p>
        </header>

        <div class="relative px-16 py-12">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-sm font-bold uppercase tracking-[0.35em] text-slate-400 text-center font-sans">Ansøgning</h2>
                    <div class="mt-8 space-y-5 text-[16px] leading-loose text-slate-800 max-w-[165mm]">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-sm font-bold uppercase tracking-[0.35em] text-slate-400 text-center font-sans">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-8 space-y-7">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[100px_1fr] gap-6">
                                <p class="text-right text-xs font-sans font-bold text-slate-400 tabular-nums pt-1.5 uppercase tracking-wider">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <div>
                                    <h3 class="text-xl font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm italic text-slate-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-slate-600 text-justify">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="text-sm font-bold uppercase tracking-[0.35em] text-slate-400 text-center font-sans">Kompetencer</h2>
                    <p class="mt-5 text-center text-sm leading-loose font-medium">
                        {{ implode('   ·   ', $skills) }}
                    </p>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-10">
                    <h2 class="text-sm font-bold uppercase tracking-[0.35em] text-slate-400 text-center font-sans">Links</h2>
                    <ul class="mt-5 text-sm space-y-1.5 text-slate-700 max-w-[165mm]">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-14 text-center font-sans text-xs text-slate-400 tracking-wide">
                @if ($user->birthdate)
                    Født {{ $user->birthdate->format('d/m/Y') }}  ·
                @endif
                {{ $user->name }}
            </footer>
        </div>
    </div>
</body>
</html>