{{-- Harbor Fog --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-slate-700 px-16 py-16">

        {{-- Luftig minimalist header --}}
        <header class="flex items-start justify-between gap-10">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-slate-400">Curriculum Vitae</p>
                <h1 class="mt-3 text-4xl font-light tracking-tight text-slate-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-base text-slate-500">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-20 h-20 object-cover shrink-0 rounded-full grayscale-30">
            @endif
        </header>

        <div class="mt-10 h-px bg-slate-200"></div>

        @if (isset($coverLetter))
            <section class="mt-10">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.3em] text-slate-400">01 — Ansøgning</h2>
                <div class="mt-5 space-y-4 text-[15px] leading-loose text-slate-600">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-10">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.3em] text-slate-400">01 — Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 space-y-8">
                    @foreach ($jobs as $job)
                        <article class="grid grid-cols-[100px_1fr] gap-8">
                            <p class="text-xs font-medium tracking-wide text-slate-400 tabular-nums pt-1">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <div>
                                <h3 class="font-semibold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                <p class="mt-0.5 text-sm text-slate-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2.5 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-12">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.3em] text-slate-400">02 — Kompetencer</h2>
                <p class="mt-4 text-sm leading-loose text-slate-600">
                    {{ implode('   ·   ', $skills) }}
                </p>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-12">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.3em] text-slate-400">03 — Links</h2>
                <ul class="mt-4 space-y-1.5 text-sm text-slate-600">
                    @foreach ($links as $link)
                        <li>
                            <span class="font-semibold text-slate-900">{{ $link->name }}:</span>
                            <a href="{{ $link->url }}" class="underline break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Kontakt som diskret bundlinje --}}
        <footer class="mt-14 pt-5 border-t border-slate-200 flex flex-wrap justify-between gap-x-8 gap-y-1 text-xs text-slate-400 tracking-wide">
            <span>{{ $user->phone }}</span>
            <span class="break-all">{{ $user->email }}</span>
            @if ($user->address || $user->city)
                <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)
                <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </footer>
    </div>
</body>
</html>