{{-- Midnight Violet --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#17102b] shadow-2xl min-h-[297mm] font-sans text-violet-100 px-12 py-12 relative overflow-hidden">

        {{-- Stjernestøv --}}
        <div class="absolute top-10 right-14 w-1.5 h-1.5 bg-fuchsia-300 rounded-full"></div>
        <div class="absolute top-24 right-32 w-1 h-1 bg-violet-300 rounded-full"></div>
        <div class="absolute top-16 right-52 w-2 h-2 bg-pink-300/60 rounded-full"></div>
        <div class="absolute top-40 right-10 w-1 h-1 bg-fuchsia-200 rounded-full"></div>

        <header class="text-center relative">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover rounded-full mx-auto ring-2 ring-fuchsia-400 ring-offset-4 ring-offset-[#17102b]">
            @endif
            <h1 class="mt-5 text-5xl font-black tracking-tight bg-gradient-to-r from-fuchsia-300 via-violet-300 to-pink-300 bg-clip-text text-transparent">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 text-lg text-violet-300">{{ $user->job_title }}</p>
            @endif
            <div class="mt-5 inline-flex flex-wrap justify-center gap-x-5 gap-y-1 text-sm text-violet-300/70 border border-violet-500/30 rounded-full px-6 py-2">
                @if ($user->phone)
                    <span>{{ $user->phone }}</span>
                @endif
                <span class="break-all">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-10 relative">
                <h2 class="text-xs font-bold uppercase tracking-[0.4em] text-fuchsia-300 text-center">✦ Ansøgning ✦</h2>
                <div class="mt-6 bg-white/5 border border-violet-500/20 rounded-2xl px-8 py-7 space-y-4 text-[15px] leading-relaxed text-violet-100/90">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-10 relative">
                <h2 class="text-xs font-bold uppercase tracking-[0.4em] text-fuchsia-300 text-center">✦ Erhvervserfaring &amp; uddannelse ✦</h2>
                <div class="mt-6 space-y-4">
                    @foreach ($jobs as $job)
                        <article class="bg-white/5 border border-violet-500/20 rounded-2xl px-7 py-5">
                            <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-xs font-bold text-fuchsia-300 whitespace-nowrap tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-sm font-medium text-violet-300">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-violet-100/70">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9 relative">
                <h2 class="text-xs font-bold uppercase tracking-[0.4em] text-fuchsia-300 text-center">✦ Kompetencer ✦</h2>
                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-sm font-semibold text-pink-200 bg-fuchsia-500/15 border border-fuchsia-400/30 px-4 py-1.5 rounded-full">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($user->birthdate)
            <p class="mt-10 text-center text-xs text-violet-400/60 relative">Fødselsdato: {{ $user->birthdate->format('d/m/Y') }}</p>
        @endif
    </div>
</body>
</html>
