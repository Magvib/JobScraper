{{-- Denim Blue --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-[#233648] grid grid-cols-[62mm_1fr]">

        {{-- Denim sidebar med syede detaljer --}}
        <aside class="bg-[#3b5b7c] text-blue-50 px-7 py-12 flex flex-col gap-9 relative" style="box-shadow: inset -4px 0 0 rgba(255,255,255,0.15);">
            <div class="absolute inset-y-0 right-1 border-r-2 border-dashed border-white/25"></div>

            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 object-cover rounded-lg border-4 border-[#f2a65a] mx-auto shadow-md">
            @endif

            <section>
                <h2 class="text-[11px] font-black uppercase tracking-[0.25em] text-[#f2a65a] border-b-2 border-dashed border-white/30 pb-2">Kontakt</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @if ($user->phone)
                        <li>{{ $user->phone }}</li>
                    @endif
                    <li class="break-all">{{ $user->email }}</li>
                    @if ($user->address || $user->city)
                        <li>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</li>
                    @endif
                    @if ($user->birthdate)
                        <li>Født {{ $user->birthdate->format('d/m/Y') }}</li>
                    @endif
                </ul>
            </section>

            @if ($skills)
                <section>
                    <h2 class="text-[11px] font-black uppercase tracking-[0.25em] text-[#f2a65a] border-b-2 border-dashed border-white/30 pb-2">Kompetencer</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($skills as $skill)
                            <li class="flex items-center gap-2">
                                <span class="text-[#f2a65a]">✦</span>{{ $skill }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section>
                    <h2 class="text-[11px] font-black uppercase tracking-[0.25em] text-[#f2a65a] border-b-2 border-dashed border-white/30 pb-2">Links</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>

        <main class="px-10 py-12">
            <header class="pb-6 border-b-4 border-double border-[#3b5b7c]">
                <h1 class="text-4xl font-black tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg text-[#d97b29] font-bold">{{ $user->job_title }}</p>
                @endif
            </header>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="text-sm font-black uppercase tracking-widest text-[#3b5b7c]">✂ Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="text-sm font-black uppercase tracking-widest text-[#3b5b7c]">✂ Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="border-2 border-[#3b5b7c] rounded-lg px-6 py-4 bg-[#f4f7fa]">
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold text-white bg-[#d97b29] px-2.5 py-1 rounded whitespace-nowrap tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-[#54708f]">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-[#35506b]">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </main>
    </div>
</body>
</html>
