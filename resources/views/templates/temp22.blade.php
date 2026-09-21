{{-- Banded Report --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-stone-800 shadow-lg min-h-[297mm] font-sans flex flex-col">

        {{-- Hoved-band --}}
        <header class="px-14 pt-12 pb-8 flex items-start justify-between gap-10 border-b-4 border-[#14532d]">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-[#14532d]">Curriculum Vitae</p>
                <h1 class="mt-3 font-serif text-5xl font-bold leading-tight text-stone-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg italic text-stone-500">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 rounded-full object-cover ring-1 ring-stone-300 ring-offset-4 shrink-0 mt-1">
            @endif
        </header>

        {{-- Kontakt-band i skovgrønt --}}
        <div class="bg-[#14532d] text-white/90 px-14 py-3 flex flex-wrap gap-x-6 gap-y-1 text-xs">
            @if ($user->phone)
                <span><span class="font-bold text-white">Tlf</span> {{ $user->phone }}</span>
            @endif
            <span class="break-all"><span class="font-bold text-white">Mail</span> {{ $user->email }}</span>
            @if ($user->address || $user->city)
                <span><span class="font-bold text-white">Adresse</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)
                <span><span class="font-bold text-white">Født</span> {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </div>

        {{-- Erhvervserfaring-band --}}
        @if (isset($coverLetter))
            <section class="px-14 py-9 flex-1">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#14532d] border-b border-stone-200 pb-2.5">Ansøgning</h2>
                <div class="mt-6 space-y-4">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="px-14 py-9 flex-1">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#14532d] border-b border-stone-200 pb-2.5">Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article>
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-stone-400 tabular-nums">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                –
                                {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-1 font-serif text-xl font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                            <p class="text-sm text-[#14532d]">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Kompetencer-band i lys gråt --}}
        @if ($skills)
            <section class="bg-stone-100 px-14 py-8">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#14532d]">Kompetencer</h2>
                <ul class="mt-5 grid grid-cols-3 gap-x-8 gap-y-2.5 text-sm text-stone-700">
                    @foreach ($skills as $skill)
                        <li class="flex items-start gap-2.5">
                            <span class="mt-[7px] w-1.5 h-1.5 bg-[#14532d] shrink-0"></span>
                            <span>{{ $skill }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Links-band --}}
        @if ($links->isNotEmpty())
            <section class="px-14 py-8">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#14532d]">Links</h2>
                <ul class="mt-5 flex flex-wrap gap-x-10 gap-y-3 text-sm">
                    @foreach ($links as $link)
                        <li>
                            <p class="font-bold text-stone-900">{{ $link->name }}</p>
                            <a href="{{ $link->url }}" class="text-[#14532d] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Footer-band i skovgrønt --}}
        <footer class="mt-auto bg-[#14532d] text-white/70 px-14 py-3 flex justify-between text-[10px] uppercase tracking-[0.3em]">
            <span>{{ $user->name }}</span>
            <span>Curriculum Vitae</span>
        </footer>
    </div>
</body>
</html>