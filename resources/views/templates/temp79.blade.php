{{-- Graph Paper --}}
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
        .graph {
            background-image:
                linear-gradient(rgba(59,130,246,0.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59,130,246,0.08) 1px, transparent 1px);
            background-size: 22px 22px;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page graph max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-slate-900 px-14 py-12">

        <header class="flex items-center gap-7 pb-7 border-b-2 border-blue-500">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover shrink-0 rounded border-2 border-blue-500">
            @endif
            <div class="flex-1">
                <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1 text-lg text-blue-600 font-medium">{{ $user->job_title }}</p>
                @endif
            </div>
            <div class="text-right text-xs text-slate-500 space-y-1 font-mono">
                @if ($user->phone)
                    <p>{{ $user->phone }}</p>
                @endif
                <p class="break-all">{{ $user->email }}</p>
                @if ($user->address || $user->city)
                    <p>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                @endif
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-8 bg-white/85 border border-blue-200 rounded px-7 py-6 shadow-sm">
                <h2 class="text-xs font-black uppercase tracking-[0.25em] text-blue-600">▲ Ansøgning</h2>
                <div class="mt-4 space-y-4 text-[15px] leading-relaxed">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="text-xs font-black uppercase tracking-[0.25em] text-blue-600">▲ Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($jobs as $job)
                        <article class="bg-white/85 border border-blue-200 rounded px-6 py-4 shadow-sm flex gap-5 items-start">
                            <p class="shrink-0 text-xs font-bold font-mono text-blue-600 bg-blue-50 border border-blue-200 rounded px-2 py-1 tabular-nums">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/y') }}–{{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/y') : 'nu' }}
                            </p>
                            <div>
                                <h3 class="font-bold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm text-slate-500 font-medium">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-1.5 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-8">
                <h2 class="text-xs font-black uppercase tracking-[0.25em] text-blue-600">▲ Kompetencer</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-xs font-bold font-mono text-blue-700 bg-white/85 border border-blue-300 px-3 py-1.5 rounded">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-10 flex justify-between font-mono text-xs text-slate-400">
            <span>{{ $user->name }}</span>
            @if ($user->birthdate)
                <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
            <span>side 1/1</span>
        </footer>
    </div>
</body>
</html>
