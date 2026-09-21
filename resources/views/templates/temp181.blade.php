{{-- Signal Flags --}}
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
        /* Marinepapir: koldt, saltrimt hvidt */
        .seafoam {
            background:
                radial-gradient(ellipse 60% 30% at 100% 0%, rgba(13, 148, 136, .08), transparent 70%),
                #f4f8f8;
        }
        /* Signalflag: rektangulært flag med snor og sving */
        .sigflag {
            position: relative;
            display: inline-block;
        }
        .sigflag::before {
            content: '';
            position: absolute;
            top: -7px; left: -8px; right: -8px;
            height: 1.5px;
            background: #1e3a5f;
            transform: rotate(-1.5deg);
        }
        /* Firkantflag (A: hvid/blå) og splittet flag (O: rød/gul) */
        .flag-sq { width: 34px; height: 24px; border: 1px solid rgba(30,58,92,.35); }
        .flag-swallow {
            width: 34px; height: 22px;
            background: linear-gradient(90deg, #dc2626 0 50%, #f59e0b 50% 100%);
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 50% 50%);
            border: 1px solid rgba(30,58,92,.3);
        }
        .flag-stripes {
            background: repeating-linear-gradient(90deg, #dc2626 0 8px, #fff 8px 16px, #1e3a5f 16px 20px, #fff 20px 28px);
        }
        .flag-diag {
            background: linear-gradient(135deg, #1e3a5f 0 50%, #fff 50% 100%);
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
    $flags = ['flag-stripes', 'flag-diag', 'bg-teal-600', 'bg-amber-400', 'bg-red-600'];
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page seafoam max-w-[210mm] mx-auto shadow-lg min-h-[297mm] font-sans text-slate-700 overflow-hidden">

        {{-- Marineblå topbanner med flag-girland --}}
        <div class="bg-[#1e3a5f] text-white px-12 pt-9 pb-7 relative overflow-hidden">
            <div class="absolute inset-0 opacity-[0.07]"
                style="background: repeating-linear-gradient(45deg, transparent 0 14px, #ffffff 14px 15px);"></div>
            <div class="relative flex items-start justify-between gap-8">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.5em] text-teal-300/90">Søkort &amp; Signaler</p>
                    <h1 class="mt-3 text-5xl font-extrabold tracking-tight leading-none">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-3 text-lg font-semibold text-teal-200/90 tracking-wide">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <div class="shrink-0 w-28 h-28 rounded-lg p-1 bg-white/10 ring-1 ring-teal-300/40">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-md">
                    </div>
                @endif
            </div>
            {{-- Girland af signalflag på en snor --}}
            <div class="relative mt-6 flex items-end gap-3 sigflag-container">
                <span class="sigflag flag-sq flag-stripes" style="transform: rotate(-2deg);"></span>
                <span class="sigflag flag-sq flag-diag" style="transform: rotate(1.5deg);"></span>
                <span class="sigflag flag-sq bg-teal-600" style="transform: rotate(-1deg);"></span>
                <span class="sigflag flag-sq bg-white" style="transform: rotate(2deg);"></span>
                <span class="sigflag flag-swallow" style="transform: rotate(-1.5deg);"></span>
                <span class="sigflag flag-sq bg-amber-400" style="transform: rotate(-2.5deg);"></span>
                <span class="sigflag flag-sq bg-red-600" style="transform: rotate(1deg);"></span>
                <span class="flex-1 h-px bg-teal-300/40 mb-3"></span>
            </div>
        </div>

        {{-- Kontaktlinjen under banneret --}}
        <div class="px-12 py-3.5 border-b border-[#1e3a5f]/15 flex flex-wrap gap-x-5 gap-y-1 text-[12px] font-semibold text-slate-600 bg-white/70">
            @if ($user->phone)<span>{{ $user->phone }}</span>@endif
            @if ($user->email)<span class="break-all">{{ $user->email }}</span>@endif
            @if ($user->address || $user->city)
                <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)<span>Født {{ $user->birthdate->format('d/m/Y') }}</span>@endif
        </div>

        <div class="px-12 py-9">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-[#1e3a5f] flex items-center gap-3">
                        <span class="flag-sq flag-diag shrink-0" style="width:22px;height:16px;"></span>Ansøgning
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-600 max-w-[160mm]">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                {{-- Hvert erhverv hejst som sit flag --}}
                <section>
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-[#1e3a5f]">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[64px_1fr] gap-5 items-start bg-white/80 border border-[#1e3a5f]/10 rounded-md p-5 shadow-sm">
                                <div class="pt-0.5">
                                    <span class="sigflag block flag-sq {{ $flags[$loop->index % 5] }}" style="width:44px;height:30px;"></span>
                                    <p class="mt-3 text-[10px] font-bold tracking-widest text-slate-500 tabular-nums uppercase leading-snug">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>— {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-[#1e3a5f] leading-snug">{{ $job['title'] }}</h3>
                                    <p class="mt-0.5 text-sm font-semibold text-teal-700">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                {{-- Kompetencer som vindser på flag --}}
                <section class="mt-10">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-[#1e3a5f]">Kompetencer</h2>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        @foreach ($skills as $skill)
                            <span class="inline-flex items-center gap-2.5 text-[13px] font-semibold text-[#1e3a5f] bg-white border border-[#1e3a5f]/15 rounded-sm pl-2.5 pr-4 py-1.5 shadow-sm"
                                style="clip-path: polygon(0 0, calc(100% - 8px) 0, 100% 50%, calc(100% - 8px) 100%, 0 100%);">
                                <span class="w-2.5 h-2.5 rounded-sm {{ ['bg-red-600', 'bg-teal-600', 'bg-amber-400', 'bg-[#1e3a5f]'][$loop->index % 4] }}"></span>
                                {{ $skill }}
                            </span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-10">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.45em] text-[#1e3a5f]">Links</h2>
                    <ul class="mt-5 space-y-2 text-sm">
                        @foreach ($links as $link)
                            <li class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-teal-600 shrink-0"></span>
                                <span class="font-semibold text-[#1e3a5f]">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-slate-600 underline decoration-teal-500/60 underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-11 pt-4 border-t border-[#1e3a5f]/20 flex justify-between text-[10px] font-bold uppercase tracking-[0.35em] text-slate-500">
                <span>{{ $user->name }}</span>
                <span class="inline-flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-[#1e3a5f] inline-block"></span>
                    <span class="w-2.5 h-2.5 bg-teal-600 inline-block"></span>
                    Signal modtaget · {{ now()->format('m.Y') }}
                </span>
            </footer>
        </div>
    </div>
</body>
</html>