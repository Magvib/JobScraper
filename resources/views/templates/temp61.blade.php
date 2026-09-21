{{-- Letterpress --}}
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
        .deboss { text-shadow: 0 1px 0 rgba(255,255,255,0.6); }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#f4efe6] shadow-lg min-h-[297mm] font-serif text-[#4a4238] px-16 py-14">

        {{-- Præget header --}}
        <header class="text-center pb-7">
            <div class="border-y-2 border-[#b8ab93] py-1">
                <div class="border-y border-[#b8ab93] py-5">
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}"
                             class="w-24 h-24 object-cover rounded-full mx-auto mb-4 sepia border-2 border-[#b8ab93]">
                    @endif
                    <h1 class="deboss text-4xl font-bold tracking-[0.2em] uppercase text-[#5c5245]">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-base italic text-[#7a6e5c]">{{ $user->job_title }}</p>
                    @endif
                </div>
            </div>
            <p class="mt-4 text-sm text-[#7a6e5c] tracking-wide">
                {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? '')), $user->phone, $user->email])->filter()->implode('  ✦  ') }}
            </p>
        </header>

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="text-center text-sm font-bold tracking-[0.35em] uppercase text-[#5c5245]">⁂ Ansøgning ⁂</h2>
                <div class="mt-6 space-y-4 leading-relaxed text-justify text-[15px]">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="text-center text-sm font-bold tracking-[0.35em] uppercase text-[#5c5245]">⁂ Erhvervserfaring &amp; uddannelse ⁂</h2>
                <div class="mt-7 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="border border-[#cfc4ac] bg-[#faf6ec] px-7 py-5 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)]">
                            <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                <h3 class="text-lg font-bold">{{ $job['title'] }}</h3>
                                <p class="text-xs font-bold tracking-widest text-[#8a7c64] whitespace-nowrap tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-sm italic text-[#8a7c64]">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-center text-sm font-bold tracking-[0.35em] uppercase text-[#5c5245]">⁂ Kompetencer ⁂</h2>
                <p class="mt-5 text-center text-sm leading-loose tracking-wide">{{ implode('  ✦  ', $skills) }}</p>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-9">
                <h2 class="text-center text-sm font-bold tracking-[0.35em] uppercase text-[#5c5245]">⁂ Links ⁂</h2>
                <ul class="mt-5 space-y-2 text-sm text-center">
                    @foreach ($links as $link)
                        <li>
                            <span class="font-semibold">{{ $link->name }}:</span>
                            <a href="{{ $link->url }}" class="underline break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-10 text-center">
            <p class="text-[10px] tracking-[0.4em] uppercase text-[#9a8c72]">
                {{ collect([$user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null, $user->city])->filter()->implode(' — ') }}
            </p>
        </footer>
    </div>
</body>
</html>
