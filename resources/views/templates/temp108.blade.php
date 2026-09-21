{{-- Atlas Grid --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-slate-800 shadow-lg min-h-[297mm] font-sans px-12 py-12 flex flex-col">

        {{-- Hoved med tynd ramme --}}
        <header class="border border-slate-200 px-9 py-7 flex items-center justify-between gap-8">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-teal-700">Curriculum Vitae</p>
                <h1 class="mt-2 text-4xl font-bold tracking-tight text-slate-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-base text-slate-500">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover border border-slate-200 shrink-0">
            @endif
        </header>

        {{-- Kontakt-celle --}}
        <div class="mt-3 border border-slate-200 bg-slate-50 px-9 py-3.5 flex flex-wrap gap-x-6 gap-y-1 text-xs text-slate-600">
            @if ($user->phone)
                <span><span class="font-bold text-slate-900">Tlf</span> {{ $user->phone }}</span>
            @endif
            <span class="break-all"><span class="font-bold text-slate-900">Mail</span> {{ $user->email }}</span>
            @if ($user->address || $user->city)
                <span><span class="font-bold text-slate-900">Adresse</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)
                <span><span class="font-bold text-slate-900">Født</span> {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </div>

        {{-- Grid med sektionsceller --}}
        <div class="mt-3 grid grid-cols-[1fr_56mm] items-start gap-3 flex-1">
            <main>
                @if (isset($coverLetter))
                    <section class="border border-slate-200 px-9 py-8 h-full">
                        <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-teal-700 border-b border-slate-200 pb-2.5">Ansøgning</h2>
                        <div class="mt-6 space-y-4">
                            {!! $coverLetter->renderContext() !!}
                        </div>
                    </section>
                @elseif ($jobs)
                    <section class="border border-slate-200 px-9 py-8">
                        <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-teal-700 border-b border-slate-200 pb-2.5">Erhvervserfaring &amp; Uddannelse</h2>
                        <div class="mt-6 space-y-5">
                            @foreach ($jobs as $job)
                                <article class="relative border-l-2 border-teal-700 pl-5">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400 tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <h3 class="mt-1 text-lg font-bold leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm text-teal-700">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            </main>

            <aside class="space-y-3">
                @if ($skills)
                    <section class="border border-slate-200 bg-slate-50 px-7 py-7">
                        <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-teal-700 border-b border-slate-200 pb-2.5">Kompetencer</h2>
                        <ul class="mt-4 space-y-2 text-sm text-slate-700">
                            @foreach ($skills as $skill)
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-[7px] w-1.5 h-1.5 bg-teal-700 shrink-0"></span>
                                    <span>{{ $skill }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($links->isNotEmpty())
                    <section class="border border-slate-200 bg-slate-50 px-7 py-7">
                        <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-teal-700 border-b border-slate-200 pb-2.5">Links</h2>
                        <ul class="mt-4 space-y-3 text-sm">
                            @foreach ($links as $link)
                                <li>
                                    <p class="font-bold text-slate-900">{{ $link->name }}</p>
                                    <a href="{{ $link->url }}" class="text-teal-700 underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </aside>
        </div>

        {{-- Footer-celle --}}
        <footer class="mt-3 border border-slate-200 px-9 py-3 flex justify-between text-[10px] uppercase tracking-[0.3em] text-slate-400">
            <span>{{ $user->name }}</span>
            <span>Curriculum Vitae</span>
        </footer>
    </div>
</body>
</html>