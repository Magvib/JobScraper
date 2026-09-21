{{-- Wax Seal --}}
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
        /* Voksmærke: ujævn kant med radial skygge + initial */
        .wax {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, #d0342c, #7f1d1d 65%);
            box-shadow: inset 0 0 10px rgba(0,0,0,.45), 2px 3px 6px rgba(0,0,0,.3);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .wax::before {
            content: '';
            position: absolute;
            inset: -5px;
            border-radius: 50%;
            background: radial-gradient(circle at 40% 35%, #b91c1c 0%, #7f1d1d 70%, transparent 72%);
            z-index: -1;
            filter: blur(0.5px);
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
    $initial = mb_strtoupper(mb_substr($user->name, 0, 1));
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#faf7f0] shadow-xl min-h-[297mm] font-serif text-stone-800 relative overflow-hidden">

        {{-- Let papirstruktur: dobbeltlinje-ramme --}}
        <div class="absolute inset-4 border border-stone-300 pointer-events-none"></div>
        <div class="absolute inset-5 border border-stone-400/50 pointer-events-none"></div>

        <div class="relative px-16 pt-16 pb-12">
            <header class="text-center">
                <div class="flex items-center justify-center gap-6">
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-20 h-20 object-cover rounded-full border border-stone-400 grayscale-20 sepia-10">
                    @endif
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.5em] text-stone-400">Curriculum Vitae</p>
                        <h1 class="mt-3 text-4xl font-bold tracking-wide text-stone-900">{{ $user->name }}</h1>
                        @if ($user->job_title)
                            <p class="mt-2 text-base italic text-red-900">{{ $user->job_title }}</p>
                        @endif
                    </div>
                </div>
                <p class="mt-6 text-[13px] text-stone-500">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </p>
                <div class="mx-auto mt-7 h-px w-40 bg-stone-300"></div>
            </header>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="text-center text-xs font-bold uppercase tracking-[0.4em] text-red-900">Ansøgning</h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-stone-700 text-justify">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="text-center text-xs font-bold uppercase tracking-[0.4em] text-red-900">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-7 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-6">
                                <div class="shrink-0 w-16 pt-1 text-right">
                                    <p class="text-[11px] font-semibold text-stone-400 tabular-nums leading-relaxed uppercase">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <div class="border-l border-stone-300 pl-6">
                                    <h3 class="font-bold text-lg leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm italic text-red-900/80">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-stone-600 text-justify">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-center text-xs font-bold uppercase tracking-[0.4em] text-red-900">Kompetencer</h2>
                    <p class="mt-5 text-center text-[14px] leading-loose text-stone-700">
                        {!! implode(' <span class="text-red-800">✦</span> ', collect($skills)->map(fn ($s) => e($s))->all()) !!}
                    </p>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-9">
                    <h2 class="text-center text-xs font-bold uppercase tracking-[0.4em] text-red-900">Links</h2>
                    <ul class="mt-5 space-y-1.5 text-[14px] text-stone-700 text-center">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold text-stone-900">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-red-900 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-12 flex flex-col items-center">
                <div class="wax">
                    <span class="text-3xl font-bold text-red-50 tracking-widest">{{ $initial }}</span>
                </div>
                <p class="mt-4 text-[10px] uppercase tracking-[0.35em] text-stone-400">
                    @if ($user->birthdate)Født {{ $user->birthdate->format('d/m/Y') }} · @endif{{ now()->format('Y') }}
                </p>
            </footer>
        </div>
    </div>
</body>
</html>