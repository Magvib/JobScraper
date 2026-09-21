{{-- Academia --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#f3ecdd] shadow-lg min-h-[297mm] font-serif text-[#2c2419] px-16 py-14 border-t-8 border-[#3d3226]">

        <header class="text-center">
            <p class="text-xs tracking-[0.4em] uppercase text-[#8a7a5f]">Curriculum Vitae</p>
            <h1 class="mt-3 text-4xl font-bold">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 italic text-lg text-[#5c4f3a]">{{ $user->job_title }}</p>
            @endif
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-28 object-cover mx-auto mt-5 sepia-[0.3] border border-[#8a7a5f] shadow-md p-1 bg-white">
            @endif
            <p class="mt-5 text-sm text-[#6f6046]">
                {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? '')), $user->phone, $user->email])->filter()->implode('  ·  ') }}
            </p>
            <div class="mt-6 flex items-center justify-center gap-4 text-[#8a7a5f]">
                <span class="h-px w-24 bg-[#c9bb9c]"></span>
                <span class="text-lg">§</span>
                <span class="h-px w-24 bg-[#c9bb9c]"></span>
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-9">
                <h2 class="text-sm font-bold tracking-[0.3em] uppercase text-center text-[#5c4f3a]">Ansøgning</h2>
                <div class="mt-6 space-y-4 leading-loose text-justify text-[15px] first-letter:text-4xl first-letter:font-bold first-letter:text-[#3d3226] first-letter:mr-1 first-letter:float-left">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-9">
                <h2 class="text-sm font-bold tracking-[0.3em] uppercase text-center text-[#5c4f3a]">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-7 space-y-7">
                    @foreach ($jobs as $job)
                        <article>
                            <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                <h3 class="text-lg font-bold">{{ $job['title'] }}</h3>
                                <p class="text-sm text-[#8a7a5f] italic whitespace-nowrap tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="italic text-[#6f6046]">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-loose text-justify text-[#403829]">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-sm font-bold tracking-[0.3em] uppercase text-center text-[#5c4f3a]">Kompetencer</h2>
                <p class="mt-4 text-center text-sm leading-loose italic text-[#403829]">{{ implode('  ·  ', $skills) }}</p>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-9">
                <h2 class="text-sm font-bold tracking-[0.3em] uppercase text-center text-[#5c4f3a]">Links</h2>
                <ul class="mt-4 space-y-1.5 text-center text-sm text-[#403829]">
                    @foreach ($links as $link)
                        <li>
                            <span class="font-semibold">{{ $link->name }}:</span>
                            <a href="{{ $link->url }}" class="underline break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-10 text-center text-xs tracking-[0.3em] uppercase text-[#8a7a5f]">
            {{ collect([$user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null, $user->city])->filter()->implode('  ·  ') }}
        </footer>
    </div>
</body>
</html>
