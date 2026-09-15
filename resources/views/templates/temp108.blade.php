{{-- Olive Atelier --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ $user->name }}</title>
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
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#f7f5ee] shadow-lg min-h-[297mm] overflow-hidden">

        {{-- Olive farvebånd med navn --}}
        <header class="bg-[#4a5236] text-[#f7f5ee] px-14 py-12 flex items-center gap-8">
            @if ($photo)
                <div class="w-24 h-24 shrink-0 border-2 border-[#f7f5ee] border-r-transparent rotate-45 p-1">
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover -rotate-45">
                </div>
            @endif
            <div>
                <h1 class="font-serif text-5xl font-bold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-sm font-sans uppercase tracking-[0.3em] text-[#c9cfae]">{{ $user->job_title }}</p>
                @endif
            </div>
        </header>

        <div class="px-14 py-11 font-serif text-stone-800">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-xs font-sans font-bold uppercase tracking-[0.35em] text-[#4a5236] border-b-2 border-[#4a5236] inline-block pb-1.5">Ansøgning</h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-loose">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-xs font-sans font-bold uppercase tracking-[0.35em] text-[#4a5236] border-b-2 border-[#4a5236] inline-block pb-1.5">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-7 space-y-7">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[130px_1fr] gap-6">
                                <div class="text-right">
                                    <p class="text-xs font-sans font-bold tracking-widest text-[#4a5236] tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    </p>
                                    <p class="text-[11px] font-sans tracking-widest text-stone-400 tabular-nums">
                                        – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <div class="border-l border-stone-400 pl-6">
                                    <h3 class="text-xl font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm italic text-stone-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-stone-700 text-justify">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="text-xs font-sans font-bold uppercase tracking-[0.35em] text-[#4a5236] border-b-2 border-[#4a5236] inline-block pb-1.5">Kompetencer</h2>
                    <div class="mt-5 grid grid-cols-2 gap-x-10">
                        @foreach ($skills as $skill)
                            <p class="flex items-center gap-3 text-sm font-medium border-b border-stone-300 py-1.5">
                                <span class="w-1.5 h-1.5 rotate-45 bg-[#4a5236] shrink-0"></span>{{ $skill }}
                            </p>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-12 pt-4 border-t border-stone-400 flex flex-wrap justify-between gap-x-8 gap-y-1 font-sans text-xs tracking-wide text-stone-500">
                <span class="break-all">{{ $user->email }}</span>
                @if ($user->phone)
                    <span>{{ $user->phone }}</span>
                @endif
                @if ($user->address || $user->city)
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>