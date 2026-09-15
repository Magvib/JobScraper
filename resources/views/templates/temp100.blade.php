{{-- Centennial --}}
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
        .gold-text {
            background: linear-gradient(100deg, #8a6a1f, #d4af37, #f2d676, #b8860b);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#101010] shadow-2xl min-h-[297mm] font-serif text-neutral-200 overflow-hidden">

        {{-- Jubilæums header med guld ramme --}}
        <header class="relative px-14 pt-14 pb-10 text-center border-b border-[#d4af37]/50">
            <div class="absolute inset-4 border border-[#d4af37]/40 pointer-events-none"></div>
            <div class="absolute inset-6 border border-[#d4af37]/20 pointer-events-none"></div>
            <div class="relative">
                @if ($photo)
                    <div class="w-28 h-28 mx-auto rounded-full p-[3px]" style="background: linear-gradient(135deg, #8a6a1f, #f2d676, #b8860b);">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-full border-4 border-[#101010]">
                    </div>
                @endif
                <p class="mt-6 text-[10px] tracking-[0.6em] uppercase text-[#b89b4a]">Curriculum Vitae</p>
                <h1 class="gold-text mt-3 text-6xl font-bold tracking-wide">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-3 text-xl italic text-neutral-400">{{ $user->job_title }}</p>
                @endif
                <div class="mt-6 flex items-center justify-center gap-3 text-[#b89b4a]">
                    <span class="h-px w-20 bg-gradient-to-r from-transparent to-[#d4af37]"></span>
                    <span class="text-[#d4af37]">✦</span>
                    <span class="h-px w-20 bg-gradient-to-l from-transparent to-[#d4af37]"></span>
                </div>
                <p class="mt-5 text-sm text-neutral-400 font-sans">
                    {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? '')), $user->phone, $user->email])->filter()->implode('  ·  ') }}
                </p>
            </div>
        </header>

        <div class="px-14 py-10">
            @if (isset($coverLetter))
                <section>
                    <h2 class="gold-text text-sm font-bold tracking-[0.4em] uppercase text-center">Ansøgning</h2>
                    <div class="mt-7 space-y-4 leading-loose text-[15px] text-neutral-300 font-sans">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="gold-text text-sm font-bold tracking-[0.4em] uppercase text-center">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-8 space-y-8">
                        @foreach ($jobs as $job)
                            <article class="text-center">
                                <p class="text-xs tracking-[0.3em] text-[#b89b4a] tabular-nums font-sans">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    —
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-2 text-2xl font-bold text-neutral-100">{{ $job['title'] }}</h3>
                                <p class="mt-1 italic text-neutral-400">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-3 leading-loose text-neutral-300 max-w-xl mx-auto font-sans">{{ $job['description'] }}</p>
                                @endif
                                <div class="mt-5 flex items-center justify-center gap-3">
                                    <span class="h-px w-12 bg-[#d4af37]/40"></span>
                                    <span class="text-[#d4af37]/60 text-xs">✦</span>
                                    <span class="h-px w-12 bg-[#d4af37]/40"></span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="gold-text text-sm font-bold tracking-[0.4em] uppercase text-center">Kompetencer</h2>
                    <p class="mt-5 text-center text-sm tracking-widest uppercase leading-loose text-neutral-300 font-sans">
                        {{ implode('   ✦   ', $skills) }}
                    </p>
                </section>
            @endif

            <footer class="mt-12 text-center">
                <p class="text-[10px] tracking-[0.6em] uppercase text-[#b89b4a] font-sans">
                    {{ collect([$user->name, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ✦  ') }}
                </p>
            </footer>
        </div>
    </div>
</body>
</html>
