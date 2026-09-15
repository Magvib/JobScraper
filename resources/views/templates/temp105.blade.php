{{-- Terra Cotta --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#faf6f0] shadow-lg min-h-[297mm] font-serif text-stone-900 overflow-hidden">

        {{-- Varm header med dobbeltlinje --}}
        <header class="px-14 pt-14 pb-8 text-center">
            @if ($photo)
                <div class="w-28 h-28 mx-auto rounded-full p-1 bg-gradient-to-br from-orange-700 via-amber-600 to-orange-700">
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-full border-4 border-[#faf6f0]">
                </div>
            @endif
            <h1 class="mt-5 text-5xl font-bold tracking-tight">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 text-lg italic text-orange-800">{{ $user->job_title }}</p>
            @endif
            <div class="mt-6 border-t-2 border-b border-stone-900 border-b-stone-400 py-2.5 font-sans text-xs uppercase tracking-[0.2em] text-stone-600">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </div>
        </header>

        <div class="px-14 pb-12">
            @if (isset($coverLetter))
                <section class="mt-6">
                    <h2 class="text-center text-xs font-bold uppercase tracking-[0.35em] text-orange-800 font-sans flex items-center gap-4">
                        <span class="flex-1 h-px bg-stone-300"></span> Ansøgning <span class="flex-1 h-px bg-stone-300"></span>
                    </h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-loose text-stone-800">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-6">
                    <h2 class="text-center text-xs font-bold uppercase tracking-[0.35em] text-orange-800 font-sans flex items-center gap-4">
                        <span class="flex-1 h-px bg-stone-300"></span> Erhvervserfaring &amp; uddannelse <span class="flex-1 h-px bg-stone-300"></span>
                    </h2>
                    <div class="mt-7 space-y-7">
                        @foreach ($jobs as $job)
                            <article class="flex gap-6">
                                <div class="shrink-0 w-20 pt-0.5 text-center">
                                    <p class="inline-block bg-orange-800 text-[#faf6f0] text-[11px] font-sans font-bold tracking-widest px-2 py-1 tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('Y') }}–{{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('Y') : 'nu' }}
                                    </p>
                                </div>
                                <div class="flex-1 border-b border-stone-300 pb-6">
                                    <h3 class="font-bold text-xl leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm italic text-stone-600">{{ $job['company'] }}</p>
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
                <section class="mt-9">
                    <h2 class="text-center text-xs font-bold uppercase tracking-[0.35em] text-orange-800 font-sans flex items-center gap-4">
                        <span class="flex-1 h-px bg-stone-300"></span> Kompetencer <span class="flex-1 h-px bg-stone-300"></span>
                    </h2>
                    <p class="mt-5 text-center text-sm leading-loose font-medium">
                        {{ implode('   ·   ', $skills) }}
                    </p>
                </section>
            @endif

            @if ($user->birthdate)
                <footer class="mt-10 pt-4 border-t border-stone-300 text-center text-xs font-sans tracking-widest uppercase text-stone-500">
                    {{ $user->name }} — Født {{ $user->birthdate->format('d/m/Y') }}
                </footer>
            @endif
        </div>
    </div>
</body>
</html>
