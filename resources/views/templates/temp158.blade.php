{{-- Duotone Split --}}
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
    <div class="cv-page relative max-w-[210mm] mx-auto bg-[#eef2f7] shadow-xl min-h-[297mm] font-sans text-slate-800 overflow-hidden flex flex-col">

        {{-- Diagonal duotone-split i toppen via baggrundstriangle --}}
        <div class="absolute inset-x-0 top-0 h-56 bg-gradient-to-br from-sky-700 to-sky-900" style="clip-path: polygon(0 0, 100% 0, 100% 30%, 0 100%);"></div>

        <header class="relative px-14 pt-12 pb-16">
            <div class="flex items-end gap-10">
                <div class="flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-sky-200">Profil</p>
                    <h1 class="mt-3 text-5xl font-extrabold tracking-tight text-white leading-none drop-shadow">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-3 text-lg font-semibold text-sky-100">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <div class="shrink-0 w-32 h-32 rounded-2xl bg-white shadow-xl p-1.5 rotate-2">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-xl">
                    </div>
                @endif
            </div>
        </header>

        <div class="relative px-14 pb-10">
            <p class="-mt-6 text-sm font-medium text-slate-500 bg-white inline-block px-5 py-3 rounded-full shadow-md border border-slate-100">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
            </p>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-sky-800 flex items-center gap-3">
                        <span class="w-8 h-1.5 rounded-full bg-sky-700"></span>Ansøgning
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-sky-800 flex items-center gap-3">
                        <span class="w-8 h-1.5 rounded-full bg-sky-700"></span>Erhvervserfaring &amp; uddannelse
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="bg-white rounded-2xl shadow-md px-7 py-5 border-l-4 border-sky-700">
                                <p class="text-[11px] font-bold tracking-widest text-sky-600 tabular-nums uppercase">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                <p class="text-sm font-medium text-slate-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-sky-800 flex items-center gap-3">
                        <span class="w-8 h-1.5 rounded-full bg-sky-700"></span>Kompetencer
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-sky-800 bg-white border border-sky-200 rounded-full px-4 py-1.5 shadow-sm">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <footer class="relative mt-auto px-14 py-7 flex justify-between text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">
            <span>{{ $user->name }}</span>
            <span>{{ now()->format('m/Y') }}</span>
        </footer>
    </div>
</body>
</html>