{{-- Edge Index --}}
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
    <div class="cv-page relative max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-slate-800 overflow-hidden">

        {{-- Tumlingsindks-faner langs højre kant --}}
        <nav class="absolute right-0 top-16 flex flex-col gap-3">
            <span class="w-9 py-3 bg-slate-800 text-white text-[10px] font-bold tracking-widest text-center rounded-l-md shadow-sm">CV</span>
            <span class="w-9 py-3 bg-teal-600 text-white text-[10px] font-bold tracking-widest text-center rounded-l-md shadow-sm">
                <span style="writing-mode: vertical-rl">{{ isset($coverLetter) ? 'BREV' : 'ERF' }}</span>
            </span>
            <span class="w-9 py-3 bg-slate-200 text-slate-600 text-[10px] font-bold tracking-widest text-center rounded-l-md">
                <span style="writing-mode: vertical-rl">KOMP</span>
            </span>
            <span class="w-9 py-3 bg-slate-200 text-slate-600 text-[10px] font-bold tracking-widest text-center rounded-l-md">
                <span style="writing-mode: vertical-rl">INFO</span>
            </span>
        </nav>

        <div class="px-14 py-13">
            <header class="pb-7 border-b-2 border-slate-800 flex items-start justify-between gap-8">
                <div>
                    <h1 class="text-4xl font-bold tracking-tight text-slate-900">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-base font-semibold text-teal-700">{{ $user->job_title }}</p>
                    @endif
                    <p class="mt-4 text-sm text-slate-500">
                        {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                    </p>
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover shrink-0 border-2 border-slate-800">
                @endif
            </header>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="flex items-center gap-3 text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900">
                        <span class="px-2 py-0.5 bg-teal-600 text-white text-[10px] tracking-widest">01</span> Ansøgning
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="flex items-center gap-3 text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900">
                        <span class="px-2 py-0.5 bg-teal-600 text-white text-[10px] tracking-widest">01</span> Erhvervserfaring &amp; uddannelse
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="border-l border-slate-300 pl-5">
                                <p class="text-[11px] font-bold tracking-widest text-teal-700 tabular-nums uppercase">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                <p class="text-sm font-medium text-slate-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="flex items-center gap-3 text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900">
                        <span class="px-2 py-0.5 bg-slate-800 text-white text-[10px] tracking-widest">02</span> Kompetencer
                    </h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-slate-700 bg-slate-100 border border-slate-300 px-3 py-1.5 rounded-md">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="mt-9">
                <h2 class="flex items-center gap-3 text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900">
                    <span class="px-2 py-0.5 bg-slate-800 text-white text-[10px] tracking-widest">03</span> Personlige oplysninger
                </h2>
                <dl class="mt-4 grid grid-cols-[130px_1fr] gap-y-2 text-sm">
                    @if ($user->phone)
                        <dt class="font-semibold text-slate-500">Telefon</dt>
                        <dd>{{ $user->phone }}</dd>
                    @endif
                    <dt class="font-semibold text-slate-500">E-mail</dt>
                    <dd class="break-all">{{ $user->email }}</dd>
                    @if ($user->address || $user->city)
                        <dt class="font-semibold text-slate-500">Adresse</dt>
                        <dd>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</dd>
                    @endif
                    @if ($user->birthdate)
                        <dt class="font-semibold text-slate-500">Fødselsdato</dt>
                        <dd>{{ $user->birthdate->format('d/m/Y') }}</dd>
                    @endif
                </dl>
            </section>

            @if ($links->isNotEmpty())
                <section class="mt-9">
                    <h2 class="flex items-center gap-3 text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900">
                        <span class="px-2 py-0.5 bg-slate-800 text-white text-[10px] tracking-widest">04</span> Links
                    </h2>
                    <ul class="mt-4 text-sm space-y-1.5 text-slate-700">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold text-slate-900">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-teal-700 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </div>
</body>
</html>