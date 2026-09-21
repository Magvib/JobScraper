{{-- Coral Wave --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-[#4a2b2b] overflow-hidden">

        {{-- Dobbelte bølger --}}
        <header class="relative bg-[#ff6f61] text-white px-12 pt-11 pb-20">
            <div class="flex items-center justify-between gap-8">
                <div>
                    <h1 class="text-5xl font-black tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-xl text-[#ffe0db]">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 object-cover rounded-full shrink-0 border-4 border-white shadow-lg">
                @endif
            </div>
            <svg class="absolute bottom-0 left-0 w-full h-10" viewBox="0 0 100 12" preserveAspectRatio="none">
                <path d="M0 12 C 20 2 35 10 50 6 C 65 2 80 10 100 4 L 100 12 Z" fill="#ffd9d4"></path>
                <path d="M0 12 C 25 6 40 12 60 8 C 80 4 90 10 100 7 L 100 12 Z" fill="#ffffff"></path>
            </svg>
        </header>

        <div class="px-12 py-8">
            <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm text-[#a1605c]">
                @if ($user->phone)
                    <span>◉ {{ $user->phone }}</span>
                @endif
                <span class="break-all">◉ {{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span>◉ {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span>◉ {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#ff6f61]">~ Ansøgning ~</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#ff6f61]">~ Erhvervserfaring &amp; uddannelse ~</h2>
                    <div class="mt-6 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="bg-[#fff3f1] rounded-xl px-7 py-5">
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold text-[#ff6f61] whitespace-nowrap tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-[#c46b64]">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-[#6b4441]">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-8">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#ff6f61]">~ Kompetencer ~</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-semibold text-[#d6483c] bg-[#ffe4e1] px-4 py-1.5 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-8">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#ff6f61]">~ Links ~</h2>
                    <ul class="mt-4 space-y-1.5 text-sm text-[#6b4441]">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-[#d6483c] underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </div>
</body>
</html>
