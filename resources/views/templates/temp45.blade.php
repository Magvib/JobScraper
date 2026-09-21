{{-- Ocean Wave --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-sky-950 overflow-hidden">

        {{-- Gradient bølge-header --}}
        <header class="relative bg-gradient-to-br from-sky-700 via-cyan-700 to-teal-600 text-white px-12 pt-12 pb-16">
            <div class="flex items-start justify-between gap-8">
                <div>
                    <h1 class="text-5xl font-black tracking-tight leading-none">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-3 text-xl text-cyan-100 font-light">{{ $user->job_title }}</p>
                    @endif
                    <div class="mt-5 flex flex-wrap gap-x-5 gap-y-1 text-sm text-cyan-50">
                        @if ($user->phone)
                            <span>{{ $user->phone }}</span>
                        @endif
                        <span class="break-all">{{ $user->email }}</span>
                        @if ($user->address || $user->city)
                            <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                        @endif
                    </div>
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-28 h-28 object-cover rounded-2xl shrink-0 border-4 border-white/60 shadow-lg">
                @endif
            </div>
            <svg class="absolute bottom-0 left-0 w-full h-8 text-white" viewBox="0 0 100 10" preserveAspectRatio="none">
                <path d="M0 10 L0 6 Q 25 0 50 5 T 100 4 L 100 10 Z" fill="currentColor"></path>
            </svg>
        </header>

        <div class="px-12 py-10">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-teal-700 flex items-center gap-3">
                        Ansøgning <span class="flex-1 h-px bg-teal-200"></span>
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-teal-700 flex items-center gap-3">
                        Erhvervserfaring &amp; uddannelse <span class="flex-1 h-px bg-teal-200"></span>
                    </h2>
                    <div class="mt-6 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="bg-sky-50/60 rounded-xl px-6 py-5 border-l-4 border-cyan-600">
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold uppercase tracking-wider text-cyan-700 whitespace-nowrap bg-cyan-100 px-2.5 py-1 rounded-full">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-teal-800">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-sky-900/80">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-teal-700 flex items-center gap-3">
                        Kompetencer <span class="flex-1 h-px bg-teal-200"></span>
                    </h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-semibold text-white bg-gradient-to-r from-sky-600 to-teal-600 px-3.5 py-1.5 rounded-full shadow-sm">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-10">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-teal-700 flex items-center gap-3">
                        Links <span class="flex-1 h-px bg-teal-200"></span>
                    </h2>
                    <ul class="mt-4 space-y-2 text-sm">
                        @foreach ($links as $link)
                            <li class="flex flex-wrap items-baseline gap-x-2">
                                <span class="font-semibold">{{ $link->name }}</span>
                                <a href="{{ $link->url }}" class="text-cyan-700 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-10 pt-5 border-t border-sky-100 flex justify-between text-xs text-sky-900/60">
                <span>{{ $user->name }}</span>
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>
