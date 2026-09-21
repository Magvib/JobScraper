{{-- Autumn Rust --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fdf8f3] shadow-lg min-h-[297mm] font-sans text-[#4a2f24]">

        {{-- Rust header med bølget underkant --}}
        <header class="bg-[#8c3b1e] text-orange-50 px-12 pt-12 pb-10 relative">
            <div class="flex items-center justify-between gap-8">
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-orange-200 text-lg">{{ $user->job_title }}</p>
                    @endif
                    <p class="mt-4 text-sm text-orange-100/90">
                        {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                    </p>
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-28 h-28 object-cover shrink-0 rounded-full border-4 border-orange-200/60 shadow-lg">
                @endif
            </div>
        </header>
        <svg class="w-full h-4 text-[#8c3b1e] block" viewBox="0 0 100 6" preserveAspectRatio="none">
            <path d="M0 0 L5 6 L10 0 L15 6 L20 0 L25 6 L30 0 L35 6 L40 0 L45 6 L50 0 L55 6 L60 0 L65 6 L70 0 L75 6 L80 0 L85 6 L90 0 L95 6 L100 0 Z" fill="currentColor"></path>
        </svg>

        <div class="px-12 py-9">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#8c3b1e] flex items-center gap-3">
                        <span class="w-8 h-px bg-[#8c3b1e]"></span> Ansøgning
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#8c3b1e] flex items-center gap-3">
                        <span class="w-8 h-px bg-[#8c3b1e]"></span> Erhvervserfaring &amp; uddannelse
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5 items-start">
                                <div class="shrink-0 w-16 text-center">
                                    <p class="text-xl font-black text-[#8c3b1e] tabular-nums">{{ \Carbon\Carbon::parse($job['startDate'])->format('Y') }}</p>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-orange-700/70">{{ !empty($job['endDate']) ? '– ' . \Carbon\Carbon::parse($job['endDate'])->format('Y') : '– nu' }}</p>
                                </div>
                                <div class="flex-1 pb-5 border-b border-dashed border-orange-300/60">
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-orange-800">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-[#5c4032]">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#8c3b1e] flex items-center gap-3">
                        <span class="w-8 h-px bg-[#8c3b1e]"></span> Kompetencer
                    </h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-semibold text-[#8c3b1e] bg-orange-100 px-3.5 py-1.5 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-9">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#8c3b1e] flex items-center gap-3">
                        <span class="w-8 h-px bg-[#8c3b1e]"></span> Links
                    </h2>
                    <ul class="mt-4 space-y-1.5 text-sm">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-[#8c3b1e] underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-9 pt-4 border-t border-orange-200 flex justify-between text-xs text-orange-900/60">
                <span>{{ $user->name }}</span>
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>
