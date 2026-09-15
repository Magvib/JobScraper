{{-- Gradient Mesh --}}
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
        /* Mesh-gradient: flere overlappende radiale farvefeltener */
        .mesh {
            background:
                radial-gradient(ellipse 55% 70% at 12% 18%, rgba(139, 92, 246, .85), transparent 62%),
                radial-gradient(ellipse 45% 60% at 85% 10%, rgba(236, 72, 153, .75), transparent 60%),
                radial-gradient(ellipse 60% 75% at 70% 90%, rgba(45, 212, 191, .8), transparent 65%),
                radial-gradient(ellipse 50% 60% at 25% 95%, rgba(59, 130, 246, .65), transparent 60%),
                linear-gradient(135deg, #312e81, #0f172a);
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-xl min-h-[297mm] font-sans text-slate-800 overflow-hidden flex flex-col">

        <header class="mesh px-14 pt-16 pb-24 text-white relative">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="absolute right-14 top-14 w-24 h-24 object-cover rounded-2xl ring-4 ring-white/40 shadow-lg">
            @endif
            <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-white/70">Profil</p>
            <h1 class="mt-4 text-6xl font-black tracking-tight leading-[0.9] drop-shadow-sm">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-4 text-xl font-semibold text-white/90">{{ $user->job_title }}</p>
            @endif
        </header>

        <div class="px-14 -mt-14 pb-10 flex-1">
            {{-- Kontakt-fritsvævende kort oven på mesh-overgangen --}}
            <div class="relative bg-white rounded-2xl shadow-xl border border-slate-100 px-7 py-5 flex items-center justify-between gap-6 flex-wrap">
                <p class="text-sm font-medium text-slate-600">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
                </p>
                <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-indigo-400">{{ now()->format('m/Y') }}</span>
            </div>

            @if (isset($coverLetter))
                <section class="mt-9">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-indigo-500 mb-5">Ansøgning</h2>
                    <div class="space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-9">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-indigo-500 mb-6">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="space-y-5">
                        @foreach ($jobs as $job)
                            <article class="rounded-2xl bg-white border border-slate-200/80 shadow-md px-7 py-5 hover:shadow-lg transition-shadow">
                                <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                    <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                    <p class="text-[11px] font-bold tracking-widest text-indigo-400 tabular-nums uppercase whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-medium text-slate-500 mt-0.5">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2.5 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-indigo-500 mb-5">Kompetencer</h2>
                    <div class="flex flex-wrap gap-2.5">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-white bg-gradient-to-r from-indigo-500 to-violet-500 rounded-full px-4 py-1.5 shadow-sm">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <footer class="px-14 py-6 bg-slate-50 border-t border-slate-100 flex justify-between text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">
            <span>{{ $user->name }}</span>
            <span>Curriculum Vitae</span>
        </footer>
    </div>
</body>
</html>