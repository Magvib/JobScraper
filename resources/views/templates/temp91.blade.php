{{-- Desert Sunrise --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-[#42312a] overflow-hidden">

        {{-- Solopgang header --}}
        <header class="relative bg-gradient-to-b from-[#ffb75e] via-[#ed8f03] to-[#c96f1e] px-12 pt-14 pb-12 text-center overflow-hidden">
            <div class="absolute -top-10 left-1/2 -translate-x-1/2 w-40 h-40 bg-[#ffe3b3] rounded-full opacity-60"></div>
            <div class="relative">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 object-cover rounded-full mx-auto mb-4 border-4 border-white/70 shadow-lg">
                @endif
                <h1 class="text-4xl font-black tracking-tight text-white drop-shadow">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg text-[#fff3df] font-medium">{{ $user->job_title }}</p>
                @endif
                <div class="mt-4 inline-flex flex-wrap justify-center gap-x-5 gap-y-1 text-sm text-white/90 bg-white/20 backdrop-blur rounded-full px-6 py-2">
                    @if ($user->phone)
                        <span>{{ $user->phone }}</span>
                    @endif
                    <span class="break-all">{{ $user->email }}</span>
                    @if ($user->address || $user->city)
                        <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                    @endif
                </div>
            </div>
        </header>

        <div class="px-12 py-9">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#c96f1e] flex items-center gap-3">
                        <span class="w-6 h-1 bg-[#ed8f03] rounded-full"></span> Ansøgning <span class="flex-1 h-px bg-[#f5d9b8]"></span>
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#c96f1e] flex items-center gap-3">
                        <span class="w-6 h-1 bg-[#ed8f03] rounded-full"></span> Erhvervserfaring &amp; uddannelse <span class="flex-1 h-px bg-[#f5d9b8]"></span>
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="relative pl-6 border-l-2 border-[#f5d9b8]">
                                <span class="absolute -left-[7px] top-1 w-3 h-3 rounded-full bg-gradient-to-br from-[#ffb75e] to-[#c96f1e]"></span>
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold text-[#c96f1e] whitespace-nowrap tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-[#a05c2c]">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-[#5d453a]">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#c96f1e] flex items-center gap-3">
                        <span class="w-6 h-1 bg-[#ed8f03] rounded-full"></span> Kompetencer <span class="flex-1 h-px bg-[#f5d9b8]"></span>
                    </h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-semibold text-[#a05c2c] bg-[#fdf0dd] px-3.5 py-1.5 rounded-full border border-[#f0d5b0]">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-9">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#c96f1e] flex items-center gap-3">
                        <span class="w-6 h-1 bg-[#ed8f03] rounded-full"></span> Links <span class="flex-1 h-px bg-[#f5d9b8]"></span>
                    </h2>
                    <ul class="mt-4 space-y-1.5 text-sm text-[#5d453a]">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-[#c96f1e] underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($user->birthdate)
                <p class="mt-8 text-xs text-[#b08d72]">Fødselsdato: {{ $user->birthdate->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>
</body>
</html>
