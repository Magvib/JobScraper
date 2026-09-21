{{-- Ruled Ledger --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-stone-800 shadow-lg min-h-[297mm] font-sans px-14 py-12 flex flex-col">

        {{-- Hoved --}}
        <header class="flex items-end justify-between gap-10 pb-6">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-[#92400e]">Curriculum Vitae</p>
                <h1 class="mt-3 font-serif text-5xl font-bold leading-tight text-stone-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg italic text-stone-500">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 rounded-full object-cover ring-1 ring-stone-300 ring-offset-4 shrink-0">
            @endif
        </header>

        {{-- Kontakt på første regellinje --}}
        <div class="border-y-2 border-stone-900 py-3 flex flex-wrap gap-x-6 gap-y-1 text-xs text-stone-600">
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

        {{-- Regnskabsagtige sektioner med overskrift på linjen --}}
        <div class="mt-10 flex-1 space-y-12">
            @if (isset($coverLetter))
                <section>
                    <div class="flex items-center gap-4 border-b-2 border-stone-900 pb-2">
                        <h2 class="text-[11px] font-bold uppercase tracking-[0.3em] text-stone-900">Ansøgning</h2>
                        <span class="flex-1"></span>
                        <span class="text-[10px] font-bold text-[#92400e]">01</span>
                    </div>
                    <div class="mt-6 space-y-4">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <div class="flex items-center gap-4 border-b-2 border-stone-900 pb-2">
                        <h2 class="text-[11px] font-bold uppercase tracking-[0.3em] text-stone-900">Erhvervserfaring &amp; Uddannelse</h2>
                        <span class="flex-1"></span>
                        <span class="text-[10px] font-bold text-[#92400e]">01</span>
                    </div>
                    <div class="mt-6 space-y-7">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[1fr_34mm] gap-8 items-baseline border-b border-stone-200 pb-6 last:border-0 last:pb-0">
                                <div>
                                    <h3 class="font-serif text-xl font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm text-[#92400e]">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                                <p class="text-right text-xs font-bold text-stone-500 tabular-nums leading-snug">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    <span class="block font-medium text-stone-400">– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}</span>
                                </p>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section>
                    <div class="flex items-center gap-4 border-b-2 border-stone-900 pb-2">
                        <h2 class="text-[11px] font-bold uppercase tracking-[0.3em] text-stone-900">Kompetencer</h2>
                        <span class="flex-1"></span>
                        <span class="text-[10px] font-bold text-[#92400e]">02</span>
                    </div>
                    <ul class="mt-5 grid grid-cols-3 gap-x-8 gap-y-2.5 text-sm text-stone-700">
                        @foreach ($skills as $skill)
                            <li class="flex items-start gap-2.5">
                                <span class="mt-[7px] w-1.5 h-1.5 bg-[#92400e] shrink-0"></span>
                                <span>{{ $skill }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section>
                    <div class="flex items-center gap-4 border-b-2 border-stone-900 pb-2">
                        <h2 class="text-[11px] font-bold uppercase tracking-[0.3em] text-stone-900">Links</h2>
                        <span class="flex-1"></span>
                        <span class="text-[10px] font-bold text-[#92400e]">03</span>
                    </div>
                    <ul class="mt-5 flex flex-wrap gap-x-12 gap-y-3 text-sm">
                        @foreach ($links as $link)
                            <li>
                                <p class="font-bold text-stone-900">{{ $link->name }}</p>
                                <a href="{{ $link->url }}" class="text-[#92400e] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        {{-- Afsluttende kraftig regel --}}
        <footer class="mt-12 border-t-2 border-stone-900 pt-3 flex justify-between text-[10px] uppercase tracking-[0.3em] text-stone-400">
            <span>{{ $user->name }}</span>
            <span>Curriculum Vitae</span>
        </footer>
    </div>
</body>
</html>