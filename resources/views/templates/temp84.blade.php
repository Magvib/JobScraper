{{-- Emerald Dark --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#071f19] shadow-2xl min-h-[297mm] font-sans text-emerald-50 px-12 py-12">

        <header class="flex items-center gap-7">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover rounded-2xl shrink-0 ring-2 ring-emerald-400/50 ring-offset-4 ring-offset-[#071f19]">
            @endif
            <div class="flex-1">
                <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-emerald-300 font-medium">{{ $user->job_title }}</p>
                @endif
            </div>
        </header>

        <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
            @if ($user->phone)
                <p class="bg-white/5 border border-emerald-900/60 rounded-lg px-4 py-2.5"><span class="text-emerald-400 text-xs uppercase tracking-wider block">Telefon</span>{{ $user->phone }}</p>
            @endif
            <p class="bg-white/5 border border-emerald-900/60 rounded-lg px-4 py-2.5"><span class="text-emerald-400 text-xs uppercase tracking-wider block">E-mail</span><span class="break-all">{{ $user->email }}</span></p>
            @if ($user->address || $user->city)
                <p class="bg-white/5 border border-emerald-900/60 rounded-lg px-4 py-2.5"><span class="text-emerald-400 text-xs uppercase tracking-wider block">Adresse</span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
            @endif
            @if ($user->birthdate)
                <p class="bg-white/5 border border-emerald-900/60 rounded-lg px-4 py-2.5"><span class="text-emerald-400 text-xs uppercase tracking-wider block">Født</span>{{ $user->birthdate->format('d/m/Y') }}</p>
            @endif
        </div>

        @if (isset($coverLetter))
            <section class="mt-9">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-emerald-300 border-b border-emerald-800/60 pb-2">Ansøgning</h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-emerald-50/90">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-9">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-emerald-300 border-b border-emerald-800/60 pb-2">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 space-y-5">
                    @foreach ($jobs as $job)
                        <article class="border-l-2 border-emerald-500 pl-5">
                            <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-xs font-bold text-emerald-300 whitespace-nowrap tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    ⟶
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-sm text-emerald-200/70 font-medium">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-emerald-50/70">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-emerald-300 border-b border-emerald-800/60 pb-2">Kompetencer</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-sm font-medium text-emerald-200 bg-emerald-500/10 border border-emerald-500/30 px-3.5 py-1.5 rounded-full">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-10 text-center text-xs uppercase tracking-[0.4em] text-emerald-700">{{ $user->name }}</footer>
    </div>
</body>
</html>
