{{-- Dossier Tabs --}}
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
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#efe9dc] shadow-xl min-h-[297mm] font-sans text-stone-800 px-12 py-12 overflow-hidden">

        {{-- Mappe-rand med faner --}}
        <div class="flex items-end gap-1.5">
            <span class="px-5 pt-3 pb-2 rounded-t-lg bg-[#e3dbc8] border border-b-0 border-stone-300 text-[11px] font-bold uppercase tracking-widest text-stone-500">Profil</span>
            <span class="px-5 pt-3 pb-2 rounded-t-lg bg-[#e3dbc8] border border-b-0 border-stone-300 text-[11px] font-bold uppercase tracking-widest text-stone-500">Ansøgning</span>
            @if (isset($coverLetter) || $jobs)
                <span class="px-5 pt-4 pb-2 rounded-t-lg bg-[#fbf9f3] border border-b-0 border-stone-300 text-[11px] font-bold uppercase tracking-widest text-stone-900 -mb-px">
                    {{ isset($coverLetter) ? 'Ansøgning' : 'Erfaring' }}
                </span>
            @endif
            <span class="flex-1 border-b border-stone-300"></span>
        </div>

        <div class="bg-[#fbf9f3] border border-stone-300 rounded-b-lg px-10 py-10 min-h-[220mm]">

            <header class="flex items-start justify-between gap-8 pb-7 border-b border-dashed border-stone-300">
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight text-stone-900">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-base font-semibold text-amber-700">{{ $user->job_title }}</p>
                    @endif
                    <p class="mt-4 text-xs tracking-wide text-stone-500 leading-relaxed">
                        {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                        @if ($user->birthdate)
                            <br>Født {{ $user->birthdate->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
                @if ($photo)
                    <div class="shrink-0 bg-white border border-stone-300 p-1.5 shadow-[3px_3px_0_0_rgba(120,113,108,0.35)]">
                        <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover grayscale-20">
                    </div>
                @endif
            </header>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="flex items-center gap-3 text-xs font-extrabold uppercase tracking-[0.3em] text-stone-900">
                        <span class="px-2 py-0.5 bg-amber-600 text-white text-[10px] tracking-widest">AKTUEL</span> Ansøgning
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="flex items-center gap-3 text-xs font-extrabold uppercase tracking-[0.3em] text-stone-900">
                        <span class="px-2 py-0.5 bg-amber-600 text-white text-[10px] tracking-widest">AKTUEL</span> Erhvervserfaring &amp; uddannelse
                    </h2>
                    <div class="mt-5 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="bg-white border border-stone-200 border-l-4 border-l-amber-600 px-6 py-4 shadow-[2px_2px_0_0_rgba(120,113,108,0.2)]">
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="font-bold text-lg leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold tracking-wider text-amber-700 tabular-nums whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-medium text-stone-500">{{ $job['company'] }}</p>
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
                    <h2 class="flex items-center gap-3 text-xs font-extrabold uppercase tracking-[0.3em] text-stone-900">
                        <span class="px-2 py-0.5 bg-amber-600 text-white text-[10px] tracking-widest">BILAG</span> Kompetencer
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-stone-700 bg-white border border-stone-300 px-3.5 py-1.5 shadow-[2px_2px_0_0_rgba(120,113,108,0.2)]">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</body>
</html>