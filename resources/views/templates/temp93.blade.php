{{-- Pop Lime --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-zinc-950 shadow-2xl min-h-[297mm] font-sans text-zinc-100 px-12 py-12">

        {{-- Neon lime på sort --}}
        <header class="pb-7 border-b-2 border-lime-400">
            <div class="flex items-center justify-between gap-8">
                <div>
                    <h1 class="text-5xl font-black tracking-tight text-lime-400 uppercase">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-lg text-zinc-300">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 object-cover shrink-0 rounded-lg ring-2 ring-lime-400"
                         style="box-shadow: 0 0 24px rgba(163,230,53,0.45);">
                @endif
            </div>
            <div class="mt-5 flex flex-wrap gap-x-6 gap-y-1 text-sm text-zinc-400">
                @if ($user->phone)
                    <span><span class="text-lime-400">▸</span> {{ $user->phone }}</span>
                @endif
                <span class="break-all"><span class="text-lime-400">▸</span> {{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span><span class="text-lime-400">▸</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span><span class="text-lime-400">▸</span> {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="text-sm font-black uppercase tracking-[0.3em] text-lime-400">// Ansøgning</h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-zinc-300">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="text-sm font-black uppercase tracking-[0.3em] text-lime-400">// Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 space-y-5">
                    @foreach ($jobs as $job)
                        <article class="bg-zinc-900 border border-zinc-800 rounded-lg px-6 py-5 hover:border-lime-400/40">
                            <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                <h3 class="text-lg font-bold leading-snug text-zinc-100">{{ $job['title'] }}</h3>
                                <p class="text-xs font-black text-lime-400 whitespace-nowrap tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    ⟶
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-sm font-semibold text-zinc-400">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-zinc-400">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-sm font-black uppercase tracking-[0.3em] text-lime-400">// Kompetencer</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-xs font-black uppercase tracking-wider text-zinc-950 bg-lime-400 px-3.5 py-1.5 rounded">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-10 pt-4 border-t border-zinc-800 text-[10px] uppercase tracking-[0.4em] text-zinc-600 text-center">
            {{ $user->name }}
        </footer>
    </div>
</body>
</html>
