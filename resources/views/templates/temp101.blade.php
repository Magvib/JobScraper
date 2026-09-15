{{-- Executive Navy --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-xl min-h-[297mm] font-sans text-slate-800 flex overflow-hidden">

        {{-- Mørkeblå sidebar --}}
        <aside class="w-[68mm] shrink-0 bg-slate-900 text-slate-200 px-8 py-12 flex flex-col">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-32 h-32 object-cover rounded-full border-4 border-slate-700 mx-auto">
            @endif

            <section class="mt-10">
                <h2 class="text-[11px] font-bold uppercase tracking-[0.25em] text-sky-300 border-b border-slate-700 pb-2">Kontakt</h2>
                <ul class="mt-4 space-y-3 text-[13px] leading-snug">
                    @if ($user->phone)
                        <li><span class="block text-[10px] uppercase tracking-widest text-slate-400">Telefon</span>{{ $user->phone }}</li>
                    @endif
                    <li class="break-all"><span class="block text-[10px] uppercase tracking-widest text-slate-400">E-mail</span>{{ $user->email }}</li>
                    @if ($user->address || $user->city)
                        <li><span class="block text-[10px] uppercase tracking-widest text-slate-400">Adresse</span>
                            {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}
                        </li>
                    @endif
                    @if ($user->birthdate)
                        <li><span class="block text-[10px] uppercase tracking-widest text-slate-400">Fødselsdato</span>{{ $user->birthdate->format('d/m/Y') }}</li>
                    @endif
                </ul>
            </section>

            @if ($skills)
                <section class="mt-10">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.25em] text-sky-300 border-b border-slate-700 pb-2">Kompetencer</h2>
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @foreach ($skills as $skill)
                            <span class="text-[11px] font-medium bg-slate-800 border border-slate-700 text-slate-200 px-2.5 py-1 rounded">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>

        {{-- Hovedkolonne --}}
        <main class="flex-1 px-10 py-12">
            <header class="pb-7 border-b-2 border-slate-900">
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg font-medium text-sky-700">{{ $user->job_title }}</p>
                @endif
            </header>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="text-xs font-bold uppercase tracking-[0.25em] text-slate-900 flex items-center gap-3">
                        <span class="h-px w-8 bg-sky-600"></span> Ansøgning
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="text-xs font-bold uppercase tracking-[0.25em] text-slate-900 flex items-center gap-3">
                        <span class="h-px w-8 bg-sky-600"></span> Erhvervserfaring &amp; uddannelse
                    </h2>
                    <div class="mt-5 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="relative pl-5 border-l-2 border-slate-200">
                                <span class="absolute -left-[7px] top-1 w-3 h-3 rounded-full bg-sky-600 border-2 border-white ring-1 ring-sky-600/40"></span>
                                <p class="text-xs font-semibold uppercase tracking-wider text-sky-700 tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 text-lg font-bold leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                <p class="text-sm font-medium text-slate-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
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
