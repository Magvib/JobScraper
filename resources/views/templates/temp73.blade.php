{{-- Deep Ocean Dark --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#0c1f33] shadow-2xl min-h-[297mm] font-sans text-slate-200 px-12 py-12">

        <header class="flex items-center gap-7 pb-8 border-b border-cyan-800/50">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover rounded-full shrink-0 ring-2 ring-cyan-500/70 ring-offset-4 ring-offset-[#0c1f33]">
            @endif
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-white">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-cyan-400 font-medium">{{ $user->job_title }}</p>
                @endif
                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-400">
                    @if ($user->phone)
                        <span>{{ $user->phone }}</span>
                    @endif
                    <span class="break-all">{{ $user->email }}</span>
                    @if ($user->address || $user->city)
                        <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                    @endif
                    @if ($user->birthdate)
                        <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-9">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-cyan-400 flex items-center gap-3">
                    <span class="w-1.5 h-5 bg-cyan-400"></span> Ansøgning
                </h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-300">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-9">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-cyan-400 flex items-center gap-3">
                    <span class="w-1.5 h-5 bg-cyan-400"></span> Erhvervserfaring &amp; uddannelse
                </h2>
                <div class="mt-6 space-y-5">
                    @foreach ($jobs as $job)
                        <article class="bg-white/5 border border-white/10 rounded-xl px-6 py-5">
                            <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                <h3 class="text-lg font-bold text-white leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-xs font-bold text-cyan-300 whitespace-nowrap tabular-nums bg-cyan-950/80 border border-cyan-800/50 px-2.5 py-1 rounded">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-sm font-medium text-cyan-200/80">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-slate-400">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-cyan-400 flex items-center gap-3">
                    <span class="w-1.5 h-5 bg-cyan-400"></span> Kompetencer
                </h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-sm font-medium text-cyan-100 bg-cyan-950/70 border border-cyan-800/50 px-3.5 py-1.5 rounded-full">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-10 pt-4 border-t border-white/10 text-center text-xs text-slate-500 tracking-widest uppercase">
            {{ $user->name }}
        </footer>
    </div>
</body>
</html>
