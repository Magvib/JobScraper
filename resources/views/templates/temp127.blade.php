{{-- Spine Title --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fbf9f4] shadow-lg min-h-[297mm] font-sans text-stone-800 flex overflow-hidden">

        {{-- Navnet løber vertikalt op ad rygsiden --}}
        <aside class="w-[26mm] shrink-0 bg-stone-900 relative">
            <h1 class="absolute left-1/2 -translate-x-1/2 bottom-8 whitespace-nowrap text-3xl font-black uppercase tracking-[0.2em] text-stone-100"
                style="writing-mode: vertical-rl; transform: translate(-50%, 0) rotate(180deg);">
                {{ $user->name }}
            </h1>
            <div class="absolute top-0 bottom-0 left-1/2 -translate-x-1/2 w-px bg-stone-700"></div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="absolute top-7 left-1/2 -translate-x-1/2 w-14 h-14 object-cover rounded-full border-2 border-amber-400">
            @endif
        </aside>

        <main class="flex-1 px-12 py-13">
            <header class="pb-6 border-b border-stone-300">
                @if ($user->job_title)
                    <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-stone-400">{{ $user->job_title }}</p>
                @endif
                <p class="text-xs text-stone-500 mt-3 leading-relaxed">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
                </p>
            </header>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-amber-600 border-b-2 border-amber-600/30 pb-2">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-amber-600 border-b-2 border-amber-600/30 pb-2">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5">
                                <div class="shrink-0 w-2 self-stretch bg-stone-200 relative">
                                    <span class="absolute top-1.5 -left-1 w-3.5 h-3.5 rounded-full bg-amber-500 border-2 border-[#fbf9f4]"></span>
                                </div>
                                <div>
                                    <p class="text-[11px] font-bold tracking-widest text-stone-400 tabular-nums uppercase">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <h3 class="mt-1 font-bold text-lg leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-stone-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-amber-600 border-b-2 border-amber-600/30 pb-2">Kompetencer</h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-stone-700 border border-stone-400 bg-white px-3 py-1.5">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-12 pt-4 border-t border-stone-300 text-xs text-stone-400 tracking-wide uppercase">
                Curriculum Vitae — {{ now()->format('Y') }}
            </footer>
        </main>
    </div>
</body>
</html>