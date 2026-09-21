{{-- Zigzag Edge --}}
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
        /* Savtakket kant som kvitteringsbånd */
        .zigzag {
            height: 14px;
            background: linear-gradient(-45deg, transparent 70%, #b91c1c 71%),
                        linear-gradient(45deg, transparent 70%, #b91c1c 71%);
            background-size: 14px 14px;
            background-position: left bottom;
            background-repeat: repeat-x;
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fdfaf5] shadow-xl min-h-[297mm] font-sans text-stone-900 overflow-hidden">

        {{-- Mørkerød kvitteringsblok med savtakket bund --}}
        <header class="bg-red-800 text-white px-14 pt-12 pb-12">
            <div class="flex items-center gap-8">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover shrink-0 rounded-full border-2 border-dashed border-red-300 p-1">
                @endif
                <div>
                    <p class="font-mono text-[10px] uppercase tracking-[0.35em] text-red-200">cv · {{ now()->format('d.m.Y') }}</p>
                    <h1 class="mt-2 text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-base font-semibold text-red-200">{{ $user->job_title }}</p>
                    @endif
                </div>
            </div>
            <p class="mt-6 font-mono text-xs text-red-100">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </p>
        </header>
        <div class="zigzag"></div>

        <div class="px-14 py-9">
            @if (isset($coverLetter))
                <section>
                    <h2 class="font-mono text-xs font-extrabold uppercase tracking-[0.3em] text-red-800 border-b border-dashed border-red-300 pb-2">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="font-mono text-xs font-extrabold uppercase tracking-[0.3em] text-red-800 border-b border-dashed border-red-300 pb-2">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[110px_1fr] gap-6">
                                <p class="text-right font-mono text-xs font-bold text-stone-400 tabular-nums pt-1.5 uppercase">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <div class="border-l-2 border-dashed border-red-400/70 pl-5">
                                    <h3 class="font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-red-700">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="font-mono text-xs font-extrabold uppercase tracking-[0.3em] text-red-800 border-b border-dashed border-red-300 pb-2">Kompetencer</h2>
                    <div class="mt-5 grid grid-cols-2 gap-x-8">
                        @foreach ($skills as $skill)
                            <p class="font-mono text-sm font-semibold text-stone-700 border-b border-dotted border-stone-300 py-1.5 flex justify-between">
                                {{ $skill }} <span class="text-red-400">✂</span>
                            </p>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-9">
                    <h2 class="font-mono text-xs font-extrabold uppercase tracking-[0.3em] text-red-800 border-b border-dashed border-red-300 pb-2">Links</h2>
                    <ul class="mt-5 font-mono text-sm space-y-1.5 text-stone-700">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-bold text-stone-900">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-red-700 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <div class="zigzag mt-10"></div>
            <footer class="mt-1 pt-4 flex justify-between font-mono text-[10px] uppercase tracking-[0.3em] text-stone-400">
                <span>{{ $user->name }}</span>
                <span>Tak for din tid</span>
            </footer>
        </div>
    </div>
</body>
</html>