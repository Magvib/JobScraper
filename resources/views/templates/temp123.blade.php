{{-- Color Chips --}}
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
    $palette = ['#e11d48', '#f59e0b', '#10b981', '#0ea5e9', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#f8f8f6] shadow-lg min-h-[297mm] font-sans text-neutral-900 px-14 py-12 overflow-hidden">

        {{-- Studiolignende header med farveprøver --}}
        <header class="flex items-start justify-between gap-8 pb-8 border-b-4 border-neutral-900">
            <div>
                <h1 class="text-4xl font-black tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-base font-semibold text-neutral-500 uppercase tracking-[0.15em]">{{ $user->job_title }}</p>
                @endif
                <p class="mt-4 text-sm text-neutral-500">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </p>
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover shrink-0 border-2 border-neutral-900">
            @endif
        </header>

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="text-sm font-black uppercase tracking-[0.2em] text-neutral-900 flex items-center gap-4">
                    Ansøgning <span class="flex-1 h-1 bg-neutral-900 rounded-full"></span>
                </h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-neutral-700">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="text-sm font-black uppercase tracking-[0.2em] text-neutral-900 flex items-center gap-4">
                    Erhvervserfaring &amp; uddannelse <span class="flex-1 h-1 bg-neutral-900 rounded-full"></span>
                </h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $i => $job)
                        <article class="flex gap-5">
                            <span class="w-3 h-3 mt-2.5 shrink-0 rotate-45" style="background: {{ $palette[$i % count($palette)] }}"></span>
                            <div>
                                <p class="text-[11px] font-bold tracking-widest text-neutral-400 tabular-nums uppercase">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="font-bold text-lg leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm font-medium text-neutral-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-neutral-600">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-10">
                <h2 class="text-sm font-black uppercase tracking-[0.2em] text-neutral-900 flex items-center gap-4">
                    Kompetencer <span class="flex-1 h-1 bg-neutral-900 rounded-full"></span>
                </h2>
                {{-- Kompetencer som pantone-prøver --}}
                <div class="mt-5 grid grid-cols-4 gap-3">
                    @foreach ($skills as $i => $skill)
                        <div class="bg-white border border-neutral-300 overflow-hidden">
                            <div class="h-12" style="background: {{ $palette[$i % count($palette)] }}"></div>
                            <p class="px-2.5 py-2 text-[11px] font-bold uppercase tracking-wide leading-tight">{{ $skill }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-sm font-black uppercase tracking-[0.2em] text-neutral-900 flex items-center gap-4">
                    Links <span class="flex-1 h-1 bg-neutral-900 rounded-full"></span>
                </h2>
                <ul class="mt-5 text-sm space-y-2 text-neutral-700">
                    @foreach ($links as $i => $link)
                        <li class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 shrink-0 rotate-45" style="background: {{ $palette[$i % count($palette)] }}"></span>
                            <span class="font-semibold text-neutral-900">{{ $link->name }}:</span>
                            <a href="{{ $link->url }}" class="underline break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-12 pt-4 border-t border-neutral-300 flex justify-between text-xs text-neutral-400 tracking-wide">
            @if ($user->birthdate)
                <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
            <span>{{ $user->name }} — {{ now()->format('Y') }}</span>
        </footer>
    </div>
</body>
</html>