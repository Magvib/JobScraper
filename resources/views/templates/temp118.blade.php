{{-- Corner Fold --}}
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
        /* Foldet hjørne øverst til højre */
        .corner {
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 170px 170px 0;
            border-color: transparent #1d4ed8 transparent transparent;
        }
        .corner-fold {
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 60px 60px 0;
            border-color: transparent #dbeafe transparent transparent;
            filter: drop-shadow(-2px 2px 3px rgba(15, 23, 42, 0.15));
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
    <div class="cv-page relative max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-slate-800 overflow-hidden">

        <div class="corner"></div>
        <div class="corner-fold"></div>

        <div class="px-14 py-14">
            <header class="pr-16 pb-8 border-b border-slate-200">
                <div class="flex items-start justify-between gap-8">
                    <div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                        @if ($user->job_title)
                            <p class="mt-2 text-base font-semibold text-blue-700">{{ $user->job_title }}</p>
                        @endif
                    </div>
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover shrink-0 border border-slate-300 shadow-sm">
                    @endif
                </div>
                <p class="mt-5 text-sm text-slate-500">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </p>
            </header>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="text-sm font-bold uppercase tracking-[0.25em] text-blue-700 flex items-center gap-3">
                        <span class="w-2 h-2 bg-blue-600 rotate-45"></span> Ansøgning
                        <span class="flex-1 h-px bg-slate-200"></span>
                    </h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="text-sm font-bold uppercase tracking-[0.25em] text-blue-700 flex items-center gap-3">
                        <span class="w-2 h-2 bg-blue-600 rotate-45"></span> Erhvervserfaring &amp; Uddannelse
                        <span class="flex-1 h-px bg-slate-200"></span>
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5">
                                <div class="shrink-0 w-16 border-r-2 border-blue-600/60 pr-4 text-right">
                                    <p class="text-sm font-bold text-slate-900 tabular-nums">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}</p>
                                    <p class="text-[11px] text-slate-400 tabular-nums">– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}</p>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
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
                    <h2 class="text-sm font-bold uppercase tracking-[0.25em] text-blue-700 flex items-center gap-3">
                        <span class="w-2 h-2 bg-blue-600 rotate-45"></span> Kompetencer
                        <span class="flex-1 h-px bg-slate-200"></span>
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-blue-800 bg-blue-50 px-3 py-1.5 rounded">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-9">
                    <h2 class="text-sm font-bold uppercase tracking-[0.25em] text-blue-700 flex items-center gap-3">
                        <span class="w-2 h-2 bg-blue-600 rotate-45"></span> Links
                        <span class="flex-1 h-px bg-slate-200"></span>
                    </h2>
                    <ul class="mt-5 text-sm space-y-1">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold text-slate-900">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-slate-600 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-12 pt-4 border-t border-slate-200 flex justify-between text-xs text-slate-400">
                <span>{{ $user->name }}</span>
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>