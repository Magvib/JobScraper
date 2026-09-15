{{-- Envelope Letter --}}
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
        /* Konvolut-klap: trekantet fold med skygge */
        .flap {
            clip-path: polygon(0 0, 100% 0, 50% 100%);
            background: linear-gradient(180deg, #d9c7a8, #cbb693);
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#efe6d4] shadow-xl min-h-[297mm] font-sans text-stone-800 overflow-hidden relative">

        {{-- Konvolut-klap i toppen med snor-lukning --}}
        <div class="flap h-40 relative">
            <div class="absolute left-1/2 bottom-5 -translate-x-1/2 flex items-center gap-3">
                <span class="block w-9 h-9 rounded-full bg-[#a3845a] shadow-inner ring-2 ring-[#8a6f49]"></span>
                <span class="block w-16 h-px bg-[#8a6f49]/70 rotate-[8deg]"></span>
            </div>
        </div>

        {{-- Frimærke-plads øverst til højre --}}
        <div class="absolute top-6 right-14 w-20 h-24 border-2 border-dashed border-[#8a6f49]/70 bg-[#f7f1e3] flex items-center justify-center rotate-2">
            <div class="text-center">
                <span class="text-2xl">📮</span>
                <p class="text-[8px] font-bold uppercase tracking-widest text-[#8a6f49] mt-1">Danmark</p>
            </div>
        </div>

        {{-- Brevet inde i konvolutten --}}
        <div class="mx-12 -mt-16 bg-[#fdfaf2] shadow-lg min-h-[240mm] border border-stone-300/60 px-14 pt-12 pb-10 relative">
            <header class="border-b border-stone-300 pb-7">
                <div class="flex items-center gap-7">
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-20 h-20 object-cover rounded-md sepia-20 border border-stone-300">
                    @endif
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.45em] text-stone-400">Brev · Curriculum Vitae</p>
                        <h1 class="mt-2 text-4xl font-bold tracking-tight text-stone-900">{{ $user->name }}</h1>
                        @if ($user->job_title)
                            <p class="mt-1.5 text-base italic text-[#8a6f49]">{{ $user->job_title }}</p>
                        @endif
                    </div>
                </div>
                <p class="mt-5 text-[13px] text-stone-500">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
                </p>
            </header>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="text-xs font-bold uppercase tracking-[0.4em] text-[#8a6f49] mb-5">Ansøgning</h2>
                    <div class="space-y-4 text-[15px] leading-relaxed text-stone-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="text-xs font-bold uppercase tracking-[0.4em] text-[#8a6f49] mb-6">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="space-y-6">
                        @foreach ($jobs as $job)
                            <article>
                                <p class="text-[11px] font-semibold tracking-widest text-stone-400 tabular-nums uppercase">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-bold text-lg leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                <p class="text-sm italic text-stone-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-xs font-bold uppercase tracking-[0.4em] text-[#8a6f49] mb-4">Kompetencer</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-stone-700 border border-[#8a6f49]/50 bg-[#f7f1e3] px-3.5 py-1.5">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</body>
</html>