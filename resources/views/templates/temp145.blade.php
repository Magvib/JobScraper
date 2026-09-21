{{-- Aquarelle Wash --}}
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
        /* Bløde vandfarvepletter i toppen */
        .wash {
            background:
                radial-gradient(ellipse 340px 190px at 18% 30%, rgba(196, 181, 253, 0.55), transparent 70%),
                radial-gradient(ellipse 320px 200px at 55% 15%, rgba(254, 215, 170, 0.55), transparent 70%),
                radial-gradient(ellipse 360px 210px at 88% 40%, rgba(165, 243, 252, 0.5), transparent 70%),
                linear-gradient(to bottom, #fdfbff, #fbf7ef);
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
    <div class="cv-page max-w-[210mm] mx-auto shadow-xl min-h-[297mm] font-sans text-slate-800 overflow-hidden">

        {{-- Vandfarvebaggrund med navn ovenpå --}}
        <header class="wash px-14 pt-16 pb-12 relative">
            <div class="relative flex items-center gap-8">
                @if ($photo)
                    <div class="w-28 h-28 shrink-0 rounded-3xl bg-white/70 backdrop-blur-sm p-2 shadow-lg">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-2xl">
                    </div>
                @endif
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-lg font-semibold text-violet-600">{{ $user->job_title }}</p>
                    @endif
                    <p class="mt-4 text-sm text-slate-600">
                        {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                    </p>
                </div>
            </div>
        </header>

        <div class="bg-[#fbf7ef] px-14 pb-12">
            @if (isset($coverLetter))
                <section class="bg-white/90 rounded-2xl shadow-md px-9 py-8 border border-white">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-800 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-br from-violet-400 to-orange-300"></span> Ansøgning
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="bg-white/90 rounded-2xl shadow-md px-9 py-8 border border-white">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-800 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-br from-violet-400 to-orange-300"></span> Erhvervserfaring &amp; uddannelse
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5">
                                <div class="shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-100 to-orange-50 border border-violet-100 flex items-center justify-center text-sm font-extrabold text-violet-700 tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('y') }}
                                </div>
                                <div>
                                    <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                        <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                        <p class="text-xs font-semibold text-slate-400 tabular-nums whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                    </div>
                                    <p class="text-sm font-medium text-violet-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-5 bg-white/90 rounded-2xl shadow-md px-9 py-8 border border-white">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-800 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-br from-cyan-300 to-violet-300"></span> Kompetencer
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-slate-700 bg-gradient-to-br from-violet-50 to-orange-50 border border-slate-200 px-4 py-1.5 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-5 bg-white/90 rounded-2xl shadow-md px-9 py-8 border border-white">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-800 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-br from-cyan-300 to-violet-300"></span> Links
                    </h2>
                    <ul class="mt-5 text-sm space-y-1.5 text-slate-700">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold text-slate-900">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-violet-600 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-9 flex justify-between text-xs text-slate-400 tracking-wide">
                <span>{{ $user->name }}</span>
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>