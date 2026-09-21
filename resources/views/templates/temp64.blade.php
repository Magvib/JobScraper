{{-- Walnut Rule --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#faf7f2] text-stone-800 shadow-lg min-h-[297mm] font-sans px-14 py-12 flex flex-col">

        {{-- Serif-hoved med valnød-accent --}}
        <header class="flex items-start justify-between gap-10 border-b-2 border-[#6b4a35] pb-7">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-[#6b4a35]">Curriculum Vitae</p>
                <h1 class="mt-3 font-serif text-5xl font-bold leading-tight text-stone-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 font-serif text-lg italic text-[#6b4a35]">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <div class="shrink-0 mt-3 border border-[#6b4a35]/60 p-1.5">
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-28 h-32 object-cover">
                </div>
            @endif
        </header>

        <div class="mt-5 text-xs text-stone-600 flex flex-wrap gap-x-4 gap-y-1">
            @if ($user->phone)
                <span><span class="font-bold text-stone-900">Tlf</span> {{ $user->phone }}</span>
            @endif
            <span class="break-all"><span class="font-bold text-stone-900">Mail</span> {{ $user->email }}</span>
            @if ($user->address || $user->city)
                <span><span class="font-bold text-stone-900">Adresse</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)
                <span><span class="font-bold text-stone-900">Født</span> {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </div>

        <div class="mt-8 grid grid-cols-[1fr_52mm] gap-10 flex-1">
            {{-- Erhvervserfaring --}}
            <main>
                @if (isset($coverLetter))
                    <h2 class="font-serif text-lg font-bold uppercase tracking-wide text-stone-900 flex items-center gap-3">
                        Ansøgning
                        <span class="flex-1 border-t border-[#6b4a35]/40"></span>
                    </h2>
                    <div class="mt-6 space-y-4">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                @elseif ($jobs)
                    <h2 class="font-serif text-lg font-bold uppercase tracking-wide text-stone-900 flex items-center gap-3">
                        Erhvervserfaring &amp; Uddannelse
                        <span class="flex-1 border-t border-[#6b4a35]/40"></span>
                    </h2>
                    <div class="mt-6 divide-y divide-stone-300/70">
                        @foreach ($jobs as $job)
                            <article class="py-5 first:pt-0 last:pb-0">
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-[#6b4a35]">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1.5 font-serif text-xl font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                <p class="text-sm italic text-stone-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </main>

            {{-- Kompetencer & links adskilt af lodret streg --}}
            @if ($skills || $links->isNotEmpty())
                <aside class="border-l border-stone-300/70 pl-8">
                    @if ($skills)
                        <h2 class="font-serif text-base font-bold uppercase tracking-wide text-stone-900">Kompetencer</h2>
                        <ul class="mt-4 space-y-2.5 text-sm text-stone-700">
                            @foreach ($skills as $skill)
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-2 w-1.5 h-1.5 bg-[#6b4a35] shrink-0"></span>
                                    <span>{{ $skill }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($links->isNotEmpty())
                        <h2 class="mt-8 font-serif text-base font-bold uppercase tracking-wide text-stone-900">Links</h2>
                        <ul class="mt-4 space-y-3 text-sm">
                            @foreach ($links as $link)
                                <li>
                                    <p class="font-bold text-stone-800">{{ $link->name }}</p>
                                    <a href="{{ $link->url }}" class="text-[#6b4a35] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </aside>
            @endif
        </div>

        {{-- Afsluttende streg --}}
        <footer class="mt-10 border-t-2 border-[#6b4a35]/60"></footer>
    </div>
</body>
</html>