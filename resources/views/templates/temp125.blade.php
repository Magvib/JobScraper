{{-- Arc Horizon --}}
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
        /* Koncentriske buer bag headeren */
        .arcs {
            background:
                radial-gradient(circle at 50% 100%, transparent 78px, #0c4a6e 78px, #0c4a6e 80px, transparent 80px),
                radial-gradient(circle at 50% 100%, transparent 98px, #0369a1 98px, #0369a1 100px, transparent 100px),
                radial-gradient(circle at 50% 100%, transparent 118px, #0284c7 118px, #0284c7 120px, transparent 120px),
                radial-gradient(circle at 50% 100%, transparent 138px, #38bdf8 138px, #38bdf8 140px, transparent 140px),
                radial-gradient(circle at 50% 100%, transparent 158px, #7dd3fc 158px, #7dd3fc 160px, transparent 160px);
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
    <div class="cv-page max-w-[210mm] mx-auto bg-sky-50 shadow-lg min-h-[297mm] font-sans text-slate-800 overflow-hidden">

        {{-- Sol-op-buer i horisonten --}}
        <header class="relative bg-white px-14 pt-14 pb-16 text-center overflow-hidden">
            <div class="arcs absolute left-1/2 -translate-x-1/2 bottom-0 w-[400px] h-[200px] opacity-90"></div>
            <div class="relative">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-28 h-28 object-cover rounded-full border-4 border-white shadow-lg mx-auto">
                @endif
                <h1 class="mt-5 text-5xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg font-semibold text-sky-700">{{ $user->job_title }}</p>
                @endif
                <p class="mt-4 text-sm text-slate-500 pb-6">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </p>
            </div>
        </header>

        <div class="px-14 py-11">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-sm font-extrabold uppercase tracking-[0.3em] text-sky-800 text-center">Ansøgning</h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-slate-700 max-w-[160mm] mx-auto">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-sm font-extrabold uppercase tracking-[0.3em] text-sky-800 text-center">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-8 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="bg-white rounded-xl shadow-sm border border-sky-100 px-7 py-5 flex gap-6">
                                <div class="shrink-0 w-px self-stretch bg-gradient-to-b from-sky-600 to-sky-200"></div>
                                <div class="flex-1">
                                    <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                        <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                        <p class="text-xs font-bold tracking-widest text-sky-600 tabular-nums whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                    </div>
                                    <p class="text-sm font-medium text-slate-500">{{ $job['company'] }}</p>
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
                <section class="mt-9">
                    <h2 class="text-sm font-extrabold uppercase tracking-[0.3em] text-sky-800 text-center">Kompetencer</h2>
                    <div class="mt-6 flex flex-wrap justify-center gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-semibold text-white bg-sky-700 px-4 py-1.5 rounded-full shadow-sm">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-9">
                    <h2 class="text-sm font-extrabold uppercase tracking-[0.3em] text-sky-800 text-center">Links</h2>
                    <ul class="mt-5 flex flex-wrap justify-center gap-x-8 gap-y-1.5 text-sm text-slate-600">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold text-slate-900">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-sky-700 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-12 text-center text-xs text-slate-400 tracking-wide">
                @if ($user->birthdate)
                    Født {{ $user->birthdate->format('d/m/Y') }}  ·
                @endif
                {{ $user->name }}
            </footer>
        </div>
    </div>
</body>
</html>