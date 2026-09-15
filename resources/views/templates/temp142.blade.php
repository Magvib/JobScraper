{{-- Edge Photo --}}
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
    <div class="cv-page relative max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-slate-800 overflow-hidden">

        {{-- Foto som halvcirkel, der bløder ud over sidens kant --}}
        @if ($photo)
            <div class="absolute top-14 -right-16 w-52 h-52 rounded-full overflow-hidden ring-1 ring-slate-200">
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
            </div>
            <div class="absolute top-24 -right-20 w-60 h-60 rounded-full border border-indigo-200 pointer-events-none"></div>
        @endif

        <div class="px-14 py-16 relative">
            <header class="pb-8 border-b border-slate-200 pr-40">
                <p class="font-mono text-[10px] uppercase tracking-[0.4em] text-indigo-500">curriculum vitae</p>
                <h1 class="mt-4 text-5xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg font-semibold text-indigo-600">{{ $user->job_title }}</p>
                @endif
                <p class="mt-5 text-sm text-slate-500">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </p>
            </header>

            @if (isset($coverLetter))
                <section class="mt-9">
                    <h2 class="text-sm font-extrabold uppercase tracking-[0.25em] text-slate-900 flex items-center gap-3">
                        <span class="w-6 h-1 bg-indigo-600"></span> Ansøgning
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-9">
                    <h2 class="text-sm font-extrabold uppercase tracking-[0.25em] text-slate-900 flex items-center gap-3">
                        <span class="w-6 h-1 bg-indigo-600"></span> Erhvervserfaring &amp; uddannelse
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[95px_1fr] gap-6">
                                <p class="text-right font-mono text-xs font-bold text-indigo-500 tabular-nums pt-1.5">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>
                                    <span class="text-slate-400">– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}</span>
                                </p>
                                <div class="border-l border-slate-200 pl-6">
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
                <section class="mt-10">
                    <h2 class="text-sm font-extrabold uppercase tracking-[0.25em] text-slate-900 flex items-center gap-3">
                        <span class="w-6 h-1 bg-indigo-600"></span> Kompetencer
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="font-mono text-[13px] text-indigo-700 bg-indigo-50 border border-indigo-100 px-3 py-1.5 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-14 pt-4 border-t border-slate-200 flex justify-between font-mono text-xs text-slate-400">
                <span>{{ $user->name }}</span>
                @if ($user->birthdate)
                    <span>{{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>