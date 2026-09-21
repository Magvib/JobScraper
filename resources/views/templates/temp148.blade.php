{{-- Barber Band --}}
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
        /* Skrastede barberstriber som accentbånd */
        .barber {
            background: repeating-linear-gradient(
                -45deg,
                #1c1917,
                #1c1917 12px,
                #b91c1c 12px,
                #b91c1c 24px
            );
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fbf8f2] shadow-xl min-h-[297mm] font-serif text-stone-900 overflow-hidden">

        {{-- Kulørret navnefelt med stribebånd under --}}
        <header class="bg-stone-900 text-[#fbf8f2] px-14 pt-12 pb-10 text-center">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-28 h-28 object-cover rounded-full mx-auto border-4 border-red-700">
            @endif
            <h1 class="mt-4 text-5xl font-bold tracking-tight">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 text-lg italic text-red-300">{{ $user->job_title }}</p>
            @endif
            <p class="mt-5 font-sans text-xs uppercase tracking-[0.25em] text-stone-300">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </p>
        </header>
        <div class="barber h-4"></div>

        <div class="px-14 py-11">
            @if (isset($coverLetter))
                <section>
                    <h2 class="font-sans text-sm font-extrabold uppercase tracking-[0.3em] text-red-800 flex items-center gap-4">
                        Ansøgning <span class="flex-1 h-0.5 bg-stone-300"></span>
                    </h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-loose text-stone-800">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="font-sans text-sm font-extrabold uppercase tracking-[0.3em] text-red-800 flex items-center gap-4">
                        Erhvervserfaring &amp; uddannelse <span class="flex-1 h-0.5 bg-stone-300"></span>
                    </h2>
                    <div class="mt-7 space-y-7">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[115px_1fr] gap-6">
                                <p class="text-right font-sans text-xs font-bold uppercase tracking-wider text-stone-400 tabular-nums pt-1.5">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <div class="border-l-2 border-red-700 pl-6">
                                    <h3 class="text-xl font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm italic text-stone-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-stone-700 text-justify">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="font-sans text-sm font-extrabold uppercase tracking-[0.3em] text-red-800 flex items-center gap-4">
                        Kompetencer <span class="flex-1 h-0.5 bg-stone-300"></span>
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        @foreach ($skills as $skill)
                            <span class="font-sans text-[13px] font-semibold text-stone-800 bg-white border border-stone-400 px-3.5 py-1.5">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-10">
                    <h2 class="font-sans text-sm font-extrabold uppercase tracking-[0.3em] text-red-800 flex items-center gap-4">
                        Links <span class="flex-1 h-0.5 bg-stone-300"></span>
                    </h2>
                    <ul class="mt-5 font-sans text-sm space-y-1.5 text-stone-800">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-red-700 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-12 pt-4 border-t-2 border-stone-900 text-center font-sans text-[10px] uppercase tracking-[0.35em] text-stone-400">
                @if ($user->birthdate)
                    Født {{ $user->birthdate->format('d/m/Y') }}  ·
                @endif
                {{ $user->name }}
            </footer>
        </div>

        <div class="barber h-4"></div>
    </div>
</body>
</html>