{{-- Roman Chapter --}}
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
    $romans = ['I', 'II', 'III', 'IV'];
    $chapter = 0;
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-stone-800 shadow-lg min-h-[297mm] font-sans px-14 py-12 flex flex-col">

        {{-- Hoved --}}
        <header class="flex items-start justify-between gap-10 pb-8 border-b-2 border-[#701a33]">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-[#701a33]">Curriculum Vitae</p>
                <h1 class="mt-3 font-serif text-5xl font-bold leading-tight text-stone-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 font-serif text-lg italic text-stone-500">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 rounded-full object-cover ring-1 ring-stone-300 ring-offset-4 shrink-0 mt-1">
            @endif
        </header>

        {{-- Kontakt-række --}}
        <div class="mt-4 flex flex-wrap gap-x-6 gap-y-1 text-xs text-stone-600">
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

        <div class="mt-9 flex-1 space-y-9">
            {{-- Kapitel I: Ansøgning / Erhvervserfaring --}}
            @if (isset($coverLetter))
                @php $chapter++ @endphp
                <section>
                    <div class="flex items-baseline gap-4 border-b border-stone-200 pb-3">
                        <span class="font-serif text-3xl font-bold text-[#701a33] leading-none">{{ $romans[$chapter - 1] }}</span>
                        <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900">Ansøgning</h2>
                    </div>
                    <div class="mt-6 space-y-4">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                @php $chapter++ @endphp
                <section>
                    <div class="flex items-baseline gap-4 border-b border-stone-200 pb-3">
                        <span class="font-serif text-3xl font-bold text-[#701a33] leading-none">{{ $romans[$chapter - 1] }}</span>
                        <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900">Erhvervserfaring &amp; Uddannelse</h2>
                    </div>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article>
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-stone-400 tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-serif text-xl font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                <p class="text-sm text-[#701a33]">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Kapitel II: Kompetencer --}}
            @if ($skills)
                @php $chapter++ @endphp
                <section>
                    <div class="flex items-baseline gap-4 border-b border-stone-200 pb-3">
                        <span class="font-serif text-3xl font-bold text-[#701a33] leading-none">{{ $romans[$chapter - 1] }}</span>
                        <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900">Kompetencer</h2>
                    </div>
                    <ul class="mt-5 grid grid-cols-3 gap-x-8 gap-y-2.5 text-sm text-stone-700">
                        @foreach ($skills as $skill)
                            <li class="flex items-start gap-2.5">
                                <span class="mt-[7px] w-1.5 h-1.5 bg-[#701a33] shrink-0"></span>
                                <span>{{ $skill }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Kapitel III: Links --}}
            @if ($links->isNotEmpty())
                @php $chapter++ @endphp
                <section>
                    <div class="flex items-baseline gap-4 border-b border-stone-200 pb-3">
                        <span class="font-serif text-3xl font-bold text-[#701a33] leading-none">{{ $romans[$chapter - 1] }}</span>
                        <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-stone-900">Links</h2>
                    </div>
                    <ul class="mt-5 flex flex-wrap gap-x-12 gap-y-3 text-sm">
                        @foreach ($links as $link)
                            <li>
                                <p class="font-bold text-stone-900">{{ $link->name }}</p>
                                <a href="{{ $link->url }}" class="text-[#701a33] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        {{-- Footer --}}
        <footer class="mt-12 border-t-2 border-[#701a33] pt-3 flex justify-between text-[10px] uppercase tracking-[0.3em] text-stone-400">
            <span>{{ $user->name }}</span>
            <span>Curriculum Vitae</span>
        </footer>
    </div>
</body>
</html>