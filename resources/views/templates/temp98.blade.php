{{-- Charcoal Noir --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-stone-900 shadow-2xl min-h-[297mm] font-sans text-stone-200 px-14 py-14">

        {{-- Noir film-header --}}
        <header class="flex items-end justify-between gap-8 pb-8 border-b border-stone-700">
            <div>
                <p class="text-[10px] uppercase tracking-[0.5em] text-stone-500">Præsenterer</p>
                <h1 class="mt-3 text-5xl font-thin tracking-wide">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-3 text-stone-400 italic font-serif text-lg">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-32 object-cover shrink-0 grayscale contrast-125 border border-stone-600">
            @endif
        </header>

        <div class="mt-5 flex flex-wrap gap-x-6 gap-y-1 text-xs uppercase tracking-widest text-stone-500">
            @if ($user->phone)
                <span>{{ $user->phone }}</span>
            @endif
            <span class="break-all lowercase">{{ $user->email }}</span>
            @if ($user->address || $user->city)
                <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)
                <span>{{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </div>

        @if (isset($coverLetter))
            <section class="mt-10">
                <h2 class="text-xs font-bold uppercase tracking-[0.4em] text-stone-400 border-l-4 border-stone-600 pl-4">Ansøgning</h2>
                <div class="mt-6 space-y-4 text-[15px] leading-loose text-stone-300">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-10">
                <h2 class="text-xs font-bold uppercase tracking-[0.4em] text-stone-400 border-l-4 border-stone-600 pl-4">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-7 space-y-8">
                    @foreach ($jobs as $job)
                        <article>
                            <p class="text-[10px] uppercase tracking-[0.3em] text-stone-500 tabular-nums">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                —
                                {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-1.5 text-2xl font-light tracking-wide">{{ $job['title'] }}</h3>
                            <p class="mt-1 text-sm text-stone-400 italic font-serif">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-3 leading-loose text-stone-400">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-10">
                <h2 class="text-xs font-bold uppercase tracking-[0.4em] text-stone-400 border-l-4 border-stone-600 pl-4">Kompetencer</h2>
                <p class="mt-5 text-sm tracking-[0.15em] uppercase leading-loose text-stone-300">{{ implode('   ·   ', $skills) }}</p>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-10">
                <h2 class="text-xs font-bold uppercase tracking-[0.4em] text-stone-400 border-l-4 border-stone-600 pl-4">Links</h2>
                <ul class="mt-5 space-y-1.5 text-sm tracking-wide text-stone-300">
                    @foreach ($links as $link)
                        <li>
                            <span class="font-semibold text-stone-100">{{ $link->name }}:</span>
                            <a href="{{ $link->url }}" class="underline break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-12 text-center text-[10px] uppercase tracking-[0.5em] text-stone-600">
            {{ $user->name }} — Slut
        </footer>
    </div>
</body>
</html>
