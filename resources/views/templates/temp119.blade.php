{{-- Monogram Letterhead --}}
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
    $initials = collect(explode(' ', (string) $user->name))->filter()->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-stone-800 px-14 py-0 overflow-hidden flex flex-col">

        {{-- Brevhoved: monogramfelt til venstre, navn til højre --}}
        <header class="pt-12 pb-8 border-b-4 border-double border-stone-900 flex items-center gap-8">
            <div class="w-24 h-24 shrink-0 bg-stone-900 text-white flex items-center justify-center font-serif text-3xl font-bold tracking-wider">
                {{ $initials }}
            </div>
            <div class="flex-1">
                <h1 class="font-serif text-4xl font-bold tracking-tight text-stone-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1 text-sm font-semibold uppercase tracking-[0.3em] text-stone-500">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-20 h-20 object-cover shrink-0 rounded-full border border-stone-300">
            @endif
        </header>

        <div class="py-10 flex-1">
            @if (isset($coverLetter))
                <section>
                    <h2 class="font-serif text-lg font-bold uppercase tracking-[0.2em] text-stone-900 border-b border-stone-300 pb-2">Ansøgning</h2>
                    <div class="mt-6 space-y-4 font-serif text-[15px] leading-relaxed text-stone-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="font-serif text-lg font-bold uppercase tracking-[0.2em] text-stone-900 border-b border-stone-300 pb-2">Erhvervserfaring &amp; Uddannelse</h2>
                    <div class="mt-7 space-y-6">
                        @foreach ($jobs as $job)
                            <article>
                                <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                    <h3 class="font-serif text-xl font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-sans font-bold tracking-widest text-stone-400 tabular-nums whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-sans font-semibold uppercase tracking-wider text-stone-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm font-sans mt-2 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="font-serif text-lg font-bold uppercase tracking-[0.2em] text-stone-900 border-b border-stone-300 pb-2">Kompetencer</h2>
                    <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2">
                        @foreach ($skills as $skill)
                            <p class="text-sm font-sans font-medium text-stone-700">{{ $skill }}</p>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-10">
                    <h2 class="font-serif text-lg font-bold uppercase tracking-[0.2em] text-stone-900 border-b border-stone-300 pb-2">Links</h2>
                    <div class="mt-5 font-serif text-sm space-y-1.5 text-stone-700">
                        @foreach ($links as $link)
                            <p>
                                <span class="font-semibold">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="underline break-all">{{ $link->prettifyUrl() }}</a>
                            </p>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        {{-- Bundlinje som på klassisk brevpapir --}}
        <footer class="border-t border-stone-300 py-5 flex flex-wrap justify-between gap-x-8 gap-y-1 text-[11px] tracking-wider text-stone-500">
            @if ($user->phone)
                <span>{{ $user->phone }}</span>
            @endif
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