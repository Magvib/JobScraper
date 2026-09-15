{{-- Slate Modern --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ $user->name }}</title>
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
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-slate-800 px-14 py-12 overflow-hidden">

        {{-- Ren header med indigo accentlinje --}}
        <header>
            <div class="flex items-end justify-between gap-8">
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-base font-semibold uppercase tracking-[0.2em] text-indigo-600">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 object-cover shrink-0 rounded-md border border-slate-200">
                @endif
            </div>
            <div class="mt-5 h-1 w-full bg-slate-200 rounded-full overflow-hidden">
                <div class="h-full w-1/3 bg-indigo-600 rounded-full"></div>
            </div>
            <dl class="mt-5 flex flex-wrap gap-x-8 gap-y-2 text-sm text-slate-600">
                @if ($user->phone)
                    <div><dt class="inline font-semibold text-slate-900">Tlf:&nbsp;</dt><dd class="inline">{{ $user->phone }}</dd></div>
                @endif
                <div><dt class="inline font-semibold text-slate-900">E-mail:&nbsp;</dt><dd class="inline break-all">{{ $user->email }}</dd></div>
                @if ($user->address || $user->city)
                    <div><dt class="inline font-semibold text-slate-900">Adresse:&nbsp;</dt><dd class="inline">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</dd></div>
                @endif
                @if ($user->birthdate)
                    <div><dt class="inline font-semibold text-slate-900">Født:&nbsp;</dt><dd class="inline">{{ $user->birthdate->format('d/m/Y') }}</dd></div>
                @endif
            </dl>
        </header>

        @if (isset($coverLetter))
            <section class="mt-9">
                <h2 class="text-sm font-bold uppercase tracking-widest text-slate-900">Ansøgning</h2>
                <div class="mt-1.5 h-0.5 w-full bg-slate-100 relative"><span class="absolute inset-y-0 left-0 w-16 bg-indigo-600"></span></div>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-9">
                <h2 class="text-sm font-bold uppercase tracking-widest text-slate-900">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-1.5 h-0.5 w-full bg-slate-100 relative"><span class="absolute inset-y-0 left-0 w-16 bg-indigo-600"></span></div>
                <div class="mt-2 divide-y divide-slate-100">
                    @foreach ($jobs as $job)
                        <article class="py-5 first:pt-4">
                            <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                <p class="text-xs font-bold uppercase tracking-wider text-indigo-600 tabular-nums whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-sm font-medium text-slate-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-sm font-bold uppercase tracking-widest text-slate-900">Kompetencer</h2>
                <div class="mt-1.5 h-0.5 w-full bg-slate-100 relative"><span class="absolute inset-y-0 left-0 w-16 bg-indigo-600"></span></div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-[13px] font-medium text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-full">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-10 pt-4 border-t border-slate-200 flex justify-between text-xs text-slate-400">
            <span>{{ $user->name }}</span>
            <span>{{ now()->format('Y') }}</span>
        </footer>
    </div>
</body>
</html>
