{{-- Pebble Soft --}}
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
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-violet-50/60 shadow-xl min-h-[297mm] font-sans text-slate-700 p-9 overflow-hidden">

        {{-- Bløde, meget afrundede paneler --}}
        <header class="bg-white rounded-[2rem] shadow-[0_8px_30px_rgba(124,58,237,0.10)] px-10 py-10 flex items-center gap-8">
            @if ($photo)
                <div class="w-28 h-28 shrink-0 rounded-[2rem] bg-violet-100 p-1.5">
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-[1.6rem]">
                </div>
            @endif
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-base font-semibold text-violet-600">{{ $user->job_title }}</p>
                @endif
                <p class="mt-4 text-sm text-slate-500 leading-relaxed">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </p>
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-6 bg-white rounded-[2rem] shadow-[0_8px_30px_rgba(124,58,237,0.10)] px-10 py-9">
                <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center text-violet-500 font-black">✦</span> Ansøgning
                </h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-6 bg-white rounded-[2rem] shadow-[0_8px_30px_rgba(124,58,237,0.10)] px-10 py-9">
                <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center text-violet-500 font-black">✦</span> Erhvervserfaring &amp; uddannelse
                </h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="bg-slate-50 rounded-2xl px-6 py-5 border border-slate-100">
                            <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                <p class="text-xs font-semibold tracking-wide text-violet-500 bg-violet-50 px-3 py-1 rounded-full tabular-nums whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-sm font-medium text-slate-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2.5 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-6 bg-white rounded-[2rem] shadow-[0_8px_30px_rgba(124,58,237,0.10)] px-10 py-9">
                <h2 class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center text-violet-500 font-black">✦</span> Kompetencer
                </h2>
                <div class="mt-5 flex flex-wrap gap-2.5">
                    @foreach ($skills as $skill)
                        <span class="text-[13px] font-semibold text-violet-700 bg-violet-50 border border-violet-100 px-4 py-2 rounded-full">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-7 px-4 pb-2 flex justify-between text-xs text-slate-400 tracking-wide">
            <span>{{ $user->name }}</span>
            @if ($user->birthdate)
                <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </footer>
    </div>
</body>
</html>