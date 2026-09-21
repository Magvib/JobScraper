{{-- Terrazzo Foyer --}}
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
        /* Terrazzogulv: tusind små skår i varm mørtel */
        .terrazzo {
            background:
                radial-gradient(circle at 6% 12%, #e07a5f 0 3px, transparent 4px),
                radial-gradient(circle at 14% 4%, #3d8a8a 0 2px, transparent 3px),
                radial-gradient(circle at 22% 18%, #e0b34c 0 2.5px, transparent 3.5px),
                radial-gradient(circle at 33% 7%, #2c2a27 0 2px, transparent 3px),
                radial-gradient(circle at 44% 15%, #e07a5f 0 2.5px, transparent 3.5px),
                radial-gradient(circle at 55% 5%, #3d8a8a 0 2px, transparent 3px),
                radial-gradient(circle at 66% 16%, #e0b34c 0 3px, transparent 4px),
                radial-gradient(circle at 78% 9%, #2c2a27 0 2px, transparent 3px),
                radial-gradient(circle at 88% 14%, #e07a5f 0 2.5px, transparent 3.5px),
                radial-gradient(circle at 96% 3%, #3d8a8a 0 2px, transparent 3px),
                #f6f1e8;
        }
        .chip {
            border-radius: 46% 54% 55% 45% / 52% 48% 52% 48%;
            display: inline-block;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
    $speckle = ['bg-[#e07a5f]', 'bg-[#3d8a8a]', 'bg-[#e0b34c]', 'bg-[#2c2a27]'];
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page terrazzo max-w-[210mm] mx-auto shadow-lg min-h-[297mm] font-sans text-stone-700 px-12 py-12">

        {{-- Terrazzo-hoved med Foto i skår-cirkel --}}
        <header class="flex items-center gap-8">
            @if ($photo)
                <div class="shrink-0 w-28 h-28 rounded-full p-1.5 bg-white shadow-md">
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-full">
                </div>
            @endif
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-[#3d8a8a]">Foyer til</p>
                <h1 class="mt-2 text-4xl font-extrabold tracking-tight text-stone-800 leading-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-lg font-semibold text-[#e07a5f]">{{ $user->job_title }}</p>
                @endif
            </div>
        </header>

        {{-- Chip-liste med kontakt --}}
        <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-2.5 text-[13px] font-semibold text-stone-600">
            <span class="chip w-2.5 h-2.5 bg-[#e07a5f]"></span>
            @if ($user->phone)<span>{{ $user->phone }}</span>@endif
            <span class="chip w-2.5 h-2.5 bg-[#3d8a8a]"></span>
            @if ($user->email)<span class="break-all">{{ $user->email }}</span>@endif
            <span class="chip w-2.5 h-2.5 bg-[#e0b34c]"></span>
            @if ($user->address || $user->city)
                <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            <span class="chip w-2.5 h-2.5 bg-[#2c2a27]"></span>
            @if ($user->birthdate)<span>Født {{ $user->birthdate->format('d/m/Y') }}</span>@endif
        </div>

        {{-- Chip-skinne ned ad venstre side --}}
        @if (isset($coverLetter))
            <section class="mt-11">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-stone-800">Ansøgning</h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-600 max-w-[155mm]">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-11">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-stone-800">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 space-y-0">
                    @foreach ($jobs as $job)
                        <article class="relative pl-10 {{ ! $loop->last ? 'pb-8' : '' }}">
                            {{-- Skinne af chips --}}
                            <span class="absolute left-[7px] top-3 bottom-0 w-px bg-stone-300 {{ $loop->last ? 'hidden' : '' }}"></span>
                            <span class="chip {{ $speckle[$loop->index % 4] }} absolute left-0 top-1.5 w-4 h-4 ring-4 ring-[#f6f1e8]"></span>
                            <p class="text-[11px] font-bold tracking-widest text-[#3d8a8a] tabular-nums uppercase">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-1 text-lg font-bold text-stone-800 leading-snug">{{ $job['title'] }}</h3>
                            <p class="text-sm font-semibold text-stone-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-stone-600 max-w-[150mm]">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-11">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-stone-800">Kompetencer</h2>
                <div class="mt-5 flex flex-wrap gap-2.5">
                    @foreach ($skills as $skill)
                        <span class="inline-flex items-center gap-2 text-[13px] font-semibold text-stone-700 bg-white/90 border border-stone-200 rounded-full pl-3 pr-4 py-1.5 shadow-sm">
                            <span class="chip {{ $speckle[$loop->index % 4] }} w-2 h-2"></span>{{ $skill }}
                        </span>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-11">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-stone-800">Links</h2>
                <ul class="mt-5 space-y-2 text-sm">
                    @foreach ($links as $link)
                        <li class="flex items-center gap-2.5">
                            <span class="chip {{ $speckle[$loop->index % 4] }} w-2 h-2 shrink-0"></span>
                            <span class="font-semibold text-stone-800">{{ $link->name }}:</span>
                            <a href="{{ $link->url }}" class="text-stone-600 underline decoration-[#e07a5f] underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-12 pt-5 border-t border-stone-300 flex justify-between items-center text-[10px] font-bold uppercase tracking-[0.3em] text-stone-400">
            <span>{{ $user->name }}</span>
            <span class="inline-flex items-center gap-2">
                <span class="chip w-2 h-2 bg-[#e07a5f]"></span>
                <span class="chip w-2 h-2 bg-[#3d8a8a]"></span>
                <span class="chip w-2 h-2 bg-[#e0b34c]"></span>
                Poleret · {{ now()->format('m.Y') }}
            </span>
        </footer>
    </div>
</body>
</html>