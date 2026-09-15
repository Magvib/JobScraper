{{-- Steel Mono --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-zinc-900 border-x-8 border-zinc-900">

        {{-- Monokrom header med stor initial --}}
        <header class="px-12 pt-12 pb-8 flex items-start gap-8 border-b border-zinc-200">
            <div class="shrink-0 w-24 h-24 bg-zinc-900 text-white flex items-center justify-center text-5xl font-black">
                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
            </div>
            <div class="flex-1">
                <h1 class="text-4xl font-black tracking-tight leading-none">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-zinc-500 uppercase tracking-widest text-sm font-semibold">{{ $user->job_title }}</p>
                @endif
                <div class="mt-4 flex flex-wrap gap-x-5 gap-y-1 text-xs text-zinc-500 uppercase tracking-wider">
                    @if ($user->phone)
                        <span>{{ $user->phone }}</span>
                    @endif
                    <span class="break-all lowercase">{{ $user->email }}</span>
                    @if ($user->address || $user->city)
                        <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                    @endif
                </div>
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="hidden">
            @endif
        </header>

        <div class="px-12 py-9">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-sm font-black uppercase tracking-widest"><span class="text-zinc-300">/</span> Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-sm font-black uppercase tracking-widest"><span class="text-zinc-300">/</span> Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-0 divide-y divide-zinc-200 border-y border-zinc-200">
                        @foreach ($jobs as $job)
                            <article class="py-4 grid grid-cols-[90px_1fr_auto] gap-5 items-baseline">
                                <p class="text-sm font-black tabular-nums">{{ \Carbon\Carbon::parse($job['startDate'])->format('Y') }}</p>
                                <div>
                                    <h3 class="font-bold leading-snug">{{ $job['title'] }} <span class="font-normal text-zinc-400">· {{ $job['company'] }}</span></h3>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-1 leading-relaxed text-zinc-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                                <p class="text-xs text-zinc-400 tabular-nums whitespace-nowrap">
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('Y') : 'nu' }}
                                </p>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-sm font-black uppercase tracking-widest"><span class="text-zinc-300">/</span> Kompetencer</h2>
                    <div class="mt-4 grid grid-cols-4 gap-px bg-zinc-200 border border-zinc-200">
                        @foreach ($skills as $skill)
                            <p class="bg-white text-xs font-bold uppercase tracking-wide text-center px-2 py-3">{{ $skill }}</p>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($user->birthdate)
                <p class="mt-9 text-xs uppercase tracking-widest text-zinc-400">Født {{ $user->birthdate->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>
</body>
</html>
