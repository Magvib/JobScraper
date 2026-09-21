{{-- Risograph Blue --}}
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
        .halftone {
            background-image: radial-gradient(circle, #1e40af 22%, transparent 24%);
            background-size: 12px 12px;
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fdfbf5] shadow-lg min-h-[297mm] font-sans text-blue-950 overflow-hidden">

        {{-- Blå halftone-rabat i toppen --}}
        <div class="h-10 halftone opacity-25 border-b-2 border-blue-800"></div>

        <header class="px-14 pt-9 pb-7 flex items-start justify-between gap-8 border-b-2 border-blue-800">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-blue-800">Curriculum Vitae</p>
                <h1 class="mt-3 text-5xl font-black tracking-tight uppercase leading-none">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2.5 text-base font-semibold uppercase tracking-[0.2em] text-blue-700">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <div class="shrink-0 border-2 border-blue-800 p-1 bg-white rotate-2">
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover grayscale contrast-125">
                </div>
            @endif
        </header>

        <div class="px-14 py-10">
            @if (isset($coverLetter))
                <section>
                    <h2 class="inline-block bg-blue-800 text-[#fdfbf5] text-xs font-extrabold uppercase tracking-[0.25em] px-4 py-2">Ansøgning</h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-blue-950/85">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="inline-block bg-blue-800 text-[#fdfbf5] text-xs font-extrabold uppercase tracking-[0.25em] px-4 py-2">Erhvervserfaring &amp; Uddannelse</h2>
                    <div class="mt-7 space-y-7">
                        @foreach ($jobs as $job)
                            <article>
                                <div class="flex items-baseline gap-4">
                                    <p class="text-xs font-extrabold uppercase tracking-widest text-blue-700 tabular-nums whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} / {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <span class="flex-1 border-b-2 border-dotted border-blue-800/40"></span>
                                </div>
                                <h3 class="mt-2 text-xl font-black uppercase tracking-tight leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm font-bold uppercase tracking-wider text-blue-700">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2.5 leading-relaxed text-blue-950/75">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="inline-block bg-blue-800 text-[#fdfbf5] text-xs font-extrabold uppercase tracking-[0.25em] px-4 py-2">Kompetencer</h2>
                    <div class="mt-6 flex flex-wrap gap-2.5">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-extrabold uppercase text-blue-800 border-2 border-blue-800 px-3.5 py-1.5 -rotate-1">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-10">
                    <h2 class="inline-block bg-blue-800 text-[#fdfbf5] text-xs font-extrabold uppercase tracking-[0.25em] px-4 py-2">Links</h2>
                    <ul class="mt-6 text-sm font-semibold text-blue-950/80">
                        @foreach ($links as $link)
                            <li>
                                <span class="uppercase text-blue-800">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-12 pt-4 border-t-2 border-blue-800 flex flex-wrap justify-between gap-x-8 gap-y-1 text-xs font-semibold tracking-wide text-blue-900/60">
                @if ($user->phone)
                    <span>{{ $user->phone }}</span>
                @endif
                <span class="break-all">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>

        {{-- Blå halftone-rabat i bunden --}}
        <div class="h-10 halftone opacity-25 border-t-2 border-blue-800"></div>
    </div>
</body>
</html>