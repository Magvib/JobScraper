{{-- Lantern Market --}}
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
        /* Aftenvarm marked: dyb blækblå med gylden hængelygte */
        .night-market {
            background:
                radial-gradient(ellipse 60% 40% at 78% 6%, rgba(250, 204, 21, .14), transparent 70%),
                radial-gradient(ellipse 45% 30% at 8% 100%, rgba(239, 68, 68, .10), transparent 70%),
                linear-gradient(180deg, #1c2333, #232b40 60%, #1a2030);
        }
        /* Hængende lanterne: snor + glødende krop */
        .lantern {
            position: relative;
            width: 26px; height: 20px;
            border-radius: 50% 50% 46% 46%;
            background: radial-gradient(ellipse at 50% 35%, #fde68a 0%, #f59e0b 55%, #b45309 100%);
            box-shadow: 0 0 14px rgba(245, 158, 11, .55);
        }
        .lantern::before {
            content: '';
            position: absolute;
            top: -12px; left: 50%;
            transform: translateX(-50%);
            width: 1px; height: 12px;
            background: linear-gradient(180deg, transparent, rgba(245, 158, 11, .8));
        }
        .lantern::after {
            content: '';
            position: absolute;
            bottom: -4px; left: 50%;
            transform: translateX(-50%);
            width: 8px; height: 3px;
            background: #b45309;
            border-radius: 2px;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page night-market max-w-[210mm] mx-auto shadow-2xl min-h-[297mm] font-sans text-slate-300 overflow-hidden px-12 py-12">

        {{-- Gylden snor hen over toppen med lanterner --}}
        <div class="relative -mx-12 px-12 pb-2">
            <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-amber-500/70 to-transparent"></div>
            <div class="flex justify-between px-14">
                <span class="lantern"></span>
                <span class="lantern" style="transform: scale(.8);"></span>
                <span class="lantern" style="transform: scale(1.1);"></span>
                <span class="lantern" style="transform: scale(.85);"></span>
                <span class="lantern" style="transform: scale(.7);"></span>
            </div>
        </div>

        <header class="mt-8 flex items-center justify-between gap-8">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.5em] text-amber-400/90">Markedsbod · Portræt</p>
                <h1 class="mt-3 text-5xl font-bold tracking-tight leading-none text-amber-50">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-3 text-lg font-medium text-amber-300/90 tracking-wide">{{ $user->job_title }}</p>
                @endif
                <p class="mt-4 text-[13px] text-slate-400">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('   ·   ') }}
                </p>
            </div>
            @if ($photo)
                <div class="shrink-0 relative">
                    <div class="absolute -inset-2 rounded-full bg-amber-500/20 blur-md"></div>
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="relative w-28 h-28 object-cover rounded-full ring-2 ring-amber-400/60 shadow-lg">
                </div>
            @endif
        </header>

        <div class="mt-6 h-px bg-gradient-to-r from-amber-600/60 via-amber-400/30 to-transparent"></div>

        @if (isset($coverLetter))
            <section class="mt-9">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-amber-400">Ansøgning</h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-300/95">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            {{-- Erhverv, der hænger fra en gylden snor --}}
            <section class="mt-9">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-amber-400">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6">
                    @foreach ($jobs as $job)
                        <article class="relative pl-10 {{ ! $loop->last ? 'pb-8' : '' }}">
                            {{-- Snoren --}}
                            <span class="absolute left-[13px] top-0 bottom-0 w-px bg-gradient-to-b from-amber-500/70 via-amber-600/40 to-amber-700/20 {{ $loop->last ? 'hidden' : '' }}"></span>
                            {{-- Lanterne i stedet for punkt --}}
                            <span class="lantern absolute left-0 top-1.5" style="width:18px;height:14px;box-shadow:0 0 10px rgba(245,158,11,.5);"></span>
                            <p class="text-[11px] font-bold tracking-widest text-amber-500/90 tabular-nums uppercase">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-1 text-xl font-semibold text-amber-50 leading-snug">{{ $job['title'] }}</h3>
                            <p class="mt-0.5 text-sm font-medium text-amber-200/70">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-slate-400">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            {{-- Kompetencer som papirfaner --}}
            <section class="mt-9">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-amber-400">Kompetencer</h2>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-[13px] font-semibold text-amber-100 bg-amber-900/40 border border-amber-600/40 rounded-md px-3.5 py-1.5"
                            style="clip-path: polygon(6px 0, 100% 0, 100% 100%, 6px 100%, 0 50%);">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-9">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-amber-400">Boder &amp; kontakter</h2>
                <ul class="mt-5 space-y-1.5 text-sm text-slate-400">
                    @foreach ($links as $link)
                        <li>
                            <span class="font-semibold text-amber-100">{{ $link->name }}:</span>
                            <a href="{{ $link->url }}" class="text-amber-300/90 underline decoration-amber-700/60 underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-12 pt-4 border-t border-amber-800/40 flex justify-between text-[10px] font-semibold uppercase tracking-[0.35em] text-slate-500">
            <span>{{ $user->name }}</span>
            <span class="inline-flex items-center gap-2">Tændt indtil sent · {{ now()->format('m.Y') }}</span>
        </footer>
    </div>
</body>
</html>