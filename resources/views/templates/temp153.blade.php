{{-- Pill Nav --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-slate-800 px-14 py-14 overflow-hidden">

        <header class="text-center pb-8">
            <div class="flex items-center justify-center gap-7">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover shrink-0 rounded-2xl border border-slate-200 shadow-sm rotate-[-2deg]">
                @endif
                <div class="text-left">
                    <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-base font-semibold text-indigo-500">{{ $user->job_title }}</p>
                    @endif
                </div>
            </div>
            <p class="mt-5 text-sm text-slate-500">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </p>

            {{-- Dekorativ pill-navigationslinje --}}
            <nav class="mt-7 flex flex-wrap justify-center gap-2">
                <span class="text-[11px] font-bold uppercase tracking-widest bg-indigo-600 text-white px-4 py-1.5 rounded-full">Profil</span>
                @if (isset($coverLetter))
                    <span class="text-[11px] font-bold uppercase tracking-widest bg-indigo-600 text-white px-4 py-1.5 rounded-full">Ansøgning</span>
                @elseif ($jobs)
                    <span class="text-[11px] font-bold uppercase tracking-widest bg-indigo-600 text-white px-4 py-1.5 rounded-full">Erfaring</span>
                @endif
                <span class="text-[11px] font-bold uppercase tracking-widest bg-slate-100 text-slate-400 px-4 py-1.5 rounded-full">Uddannelse</span>
                @if ($skills)
                    <span class="text-[11px] font-bold uppercase tracking-widest bg-slate-100 text-slate-400 px-4 py-1.5 rounded-full">Kompetencer</span>
                @endif
                <span class="text-[11px] font-bold uppercase tracking-widest bg-slate-100 text-slate-400 px-4 py-1.5 rounded-full">Kontakt</span>
            </nav>
        </header>

        <div class="h-px bg-slate-200"></div>

        @if (isset($coverLetter))
            <section class="mt-8">
                <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-slate-700">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="bg-slate-50 rounded-xl border border-slate-100 px-7 py-5 flex items-start gap-5">
                            <span class="shrink-0 mt-1.5 w-2.5 h-2.5 rounded-full bg-indigo-500 ring-4 ring-indigo-100"></span>
                            <div class="flex-1">
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold tracking-wide text-indigo-500 bg-white border border-indigo-100 px-3 py-1 rounded-full tabular-nums whitespace-nowrap">
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
            <section class="mt-8">
                <div class="flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-[13px] font-semibold text-indigo-700 bg-indigo-50 px-4 py-1.5 rounded-full border border-indigo-100">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-8">
                <div class="flex flex-wrap gap-2">
                    @foreach ($links as $link)
                        <a href="{{ $link->url }}" class="text-[13px] font-semibold text-indigo-700 bg-indigo-50 px-4 py-1.5 rounded-full border border-indigo-100 break-all">
                            {{ $link->name }}: {{ $link->prettifyUrl() }}
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-12 pt-4 border-t border-slate-200 text-center text-xs text-slate-400 tracking-wide">
            @if ($user->birthdate)
                Født {{ $user->birthdate->format('d/m/Y') }}  ·
            @endif
            {{ $user->name }}
        </footer>
    </div>
</body>
</html>