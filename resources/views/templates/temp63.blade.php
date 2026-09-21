{{-- Steel Bands --}}
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
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-slate-900">

        {{-- Bånd-struktur header --}}
        <header class="bg-slate-700 text-white px-12 py-9 flex items-center justify-between gap-8">
            <div>
                <h1 class="text-4xl font-bold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-slate-300 text-lg">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover shrink-0 border-4 border-slate-400">
            @endif
        </header>
        <div class="bg-stone-200 px-12 py-2 flex flex-wrap gap-x-6 gap-y-1 text-sm text-stone-700 border-y border-stone-300">
            @if ($user->phone)
                <span>{{ $user->phone }}</span>
            @endif
            <span class="break-all">{{ $user->email }}</span>
            @if ($user->address || $user->city)
                <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
        </div>

        <div class="px-12 py-9">
            @if (isset($coverLetter))
                <section>
                    <h2 class="bg-slate-700 text-white text-xs font-bold uppercase tracking-[0.25em] px-4 py-2 inline-block">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="bg-slate-700 text-white text-xs font-bold uppercase tracking-[0.25em] px-4 py-2 inline-block">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[130px_1fr] gap-6 py-5 {{ !$loop->first ? 'border-t-2 border-slate-200' : '' }}">
                                <div class="bg-stone-100 border border-stone-200 px-3 py-2 h-fit text-center">
                                    <p class="text-xs font-bold text-slate-700 tabular-nums">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}</p>
                                    <p class="text-[10px] text-slate-500 uppercase">til</p>
                                    <p class="text-xs font-bold text-slate-700 tabular-nums">{{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}</p>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-semibold text-slate-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-slate-700">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="bg-slate-700 text-white text-xs font-bold uppercase tracking-[0.25em] px-4 py-2 inline-block">Kompetencer</h2>
                    <div class="mt-4 bg-stone-100 border border-stone-200 px-5 py-4">
                        <p class="text-sm leading-loose text-slate-800">{{ implode('   |   ', $skills) }}</p>
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-9">
                    <h2 class="bg-slate-700 text-white text-xs font-bold uppercase tracking-[0.25em] px-4 py-2 inline-block">Links</h2>
                    <ul class="mt-3 space-y-1.5 text-sm text-slate-700">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold text-slate-900">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="mt-9">
                <h2 class="bg-slate-700 text-white text-xs font-bold uppercase tracking-[0.25em] px-4 py-2 inline-block">Personligt</h2>
                <p class="mt-3 text-sm text-slate-700">
                    {{ collect([$user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null, collect([$user->address, $user->zip, $user->city])->filter()->implode(', ')])->filter()->implode('  ·  ') }}
                </p>
            </section>
        </div>
    </div>
</body>
</html>
