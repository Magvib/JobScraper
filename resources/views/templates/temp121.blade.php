{{-- Outline Display --}}
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
        /* Hul, kontureret displaytypografi */
        .outline-name {
            -webkit-text-stroke: 2px #f5f2ea;
            color: transparent;
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#f5f2ea] shadow-xl min-h-[297mm] font-sans text-stone-800 overflow-hidden">

        {{-- Mørk skovgrøn blok med konturstreget navn --}}
        <header class="bg-stone-900 px-14 pt-14 pb-12 relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-56 h-56 rounded-full border border-emerald-400/20"></div>
            <div class="absolute -right-10 -top-10 w-72 h-72 rounded-full border border-emerald-400/10"></div>
            <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-emerald-400">Curriculum Vitae</p>
            <h1 class="outline-name mt-4 text-6xl font-black uppercase tracking-tight leading-none break-words">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-4 text-lg font-semibold text-emerald-300">{{ $user->job_title }}</p>
            @endif
            <div class="mt-7 flex items-center gap-7">
                @if ($photo)
                    <div class="w-20 h-20 shrink-0 border-2 border-emerald-400 p-1">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover grayscale">
                    </div>
                @endif
                <div class="text-xs text-stone-300 leading-relaxed">
                    @if ($user->phone)
                        <p>{{ $user->phone }}</p>
                    @endif
                    <p class="break-all">{{ $user->email }}</p>
                    @if ($user->address || $user->city)
                        <p>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                    @endif
                </div>
            </div>
        </header>

        <div class="px-14 py-11">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-xs font-black uppercase tracking-[0.35em] text-stone-900 border-b-2 border-stone-900 pb-2">Ansøgning</h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-stone-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-xs font-black uppercase tracking-[0.35em] text-stone-900 border-b-2 border-stone-900 pb-2">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-7 space-y-7">
                        @foreach ($jobs as $job)
                            <article class="flex gap-6">
                                <p class="shrink-0 w-20 text-sm font-black text-emerald-700 tabular-nums leading-snug">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('Y') }}<br>
                                    <span class="text-stone-400 font-bold">– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('Y') : 'nu' }}</span>
                                </p>
                                <div class="flex-1 border-l-2 border-stone-900 pl-6">
                                    <h3 class="text-xl font-extrabold uppercase leading-snug tracking-tight text-stone-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-semibold text-stone-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2.5 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="text-xs font-black uppercase tracking-[0.35em] text-stone-900 border-b-2 border-stone-900 pb-2">Kompetencer</h2>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-bold uppercase tracking-wide text-stone-900 border-2 border-stone-900 bg-[#f5f2ea] px-3.5 py-1.5 shadow-[3px_3px_0_0_#1c2e21]">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-10">
                    <h2 class="text-xs font-black uppercase tracking-[0.35em] text-stone-900 border-b-2 border-stone-900 pb-2">Links</h2>
                    <ul class="mt-5 text-sm space-y-1 text-stone-700">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-bold text-stone-900">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-emerald-700 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-12 pt-4 border-t border-stone-400 flex justify-between text-xs text-stone-500 tracking-wide">
                <span>{{ $user->name }}</span>
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>