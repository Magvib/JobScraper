{{-- Metro Map --}}
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
        /* Metro-linje med 45°-knæk som i et linjekort */
        .line {
            height: 10px;
            background: #2563eb;
            border-radius: 9999px;
            position: relative;
        }
        .station {
            position: absolute;
            left: -6px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            background: white;
            border: 4px solid #2563eb;
            border-radius: 9999px;
        }
        .station.end { background: #2563eb; border-color: #1e40af; }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
    $lineColors = ['bg-blue-600', 'bg-emerald-600', 'bg-amber-500', 'bg-rose-600', 'bg-violet-600'];
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#f6f7fa] shadow-xl min-h-[297mm] font-sans text-slate-800 overflow-hidden">

        {{-- Linjekort-header: logo-plade + netplan-baggrund --}}
        <header class="px-14 pt-12 pb-8 bg-white border-b-4 border-blue-600">
            <div class="flex items-center gap-7">
                @if ($photo)
                    <div class="shrink-0 w-20 h-20 rounded-2xl bg-blue-600 p-1 shadow-md">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-xl">
                    </div>
                @endif
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-600">Metrolinje · Karriereplan</p>
                    <h1 class="mt-2 text-4xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1 text-base font-semibold text-slate-500">{{ $user->job_title }}</p>
                    @endif
                </div>
                <span class="ml-auto shrink-0 w-14 h-14 rounded-full bg-slate-900 text-white flex items-center justify-center text-xl font-black">M</span>
            </div>
            <p class="mt-5 text-sm text-slate-500">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
            </p>
        </header>

        <div class="px-14 py-9">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-xs font-black uppercase tracking-[0.3em] text-blue-600 mb-5">Ansøgning</h2>
                    <div class="space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-xs font-black uppercase tracking-[0.3em] text-blue-600 mb-7">Erhvervserfaring &amp; uddannelse</h2>
                    {{-- Samlet linje med stationer --}}
                    <div class="line ml-3 mb-9">
                        <span class="station end"></span>
                        @foreach ($jobs as $i => $job)
                            <span class="station" style="left: {{ min(8 + $i * 30, 82) }}%;"></span>
                        @endforeach
                        <span class="station end" style="left: 94%;"></span>
                    </div>
                    <div class="space-y-5">
                        @foreach ($jobs as $i => $job)
                            <article class="bg-white rounded-lg border border-slate-200 px-6 py-4 flex items-start gap-5 shadow-sm">
                                <span class="shrink-0 w-9 h-9 rounded-full {{ $lineColors[$i % count($lineColors)] }} text-white text-sm font-extrabold flex items-center justify-center">{{ $i + 1 }}</span>
                                <div class="flex-1">
                                    <p class="text-[11px] font-bold tracking-widest text-slate-400 tabular-nums uppercase">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <h3 class="mt-0.5 font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-slate-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-xs font-black uppercase tracking-[0.3em] text-blue-600 mb-4">Zonekort · Kompetencer</h2>
                    <div class="flex flex-wrap gap-2.5">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-bold text-white rounded-full px-4 py-1.5 {{ $lineColors[$loop->index % count($lineColors)] }}">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-9">
                    <h2 class="text-xs font-black uppercase tracking-[0.3em] text-blue-600 mb-4">Forbindelser · Links</h2>
                    <ul class="space-y-2 text-sm">
                        @foreach ($links as $link)
                            <li class="bg-white rounded-lg border border-slate-200 px-5 py-2.5 shadow-sm flex items-center gap-3">
                                <span class="shrink-0 w-4 h-4 rounded-full {{ $lineColors[$loop->index % count($lineColors)] }}"></span>
                                <span class="font-bold text-slate-900">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-blue-600 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-11 pt-4 border-t border-dashed border-slate-300 flex justify-between text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400">
                <span>{{ $user->name }} · alle linjer</span>
                <span> {{ now()->format('Y') }} </span>
            </footer>
        </div>
    </div>
</body>
</html>