{{-- Executive Emerald --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-gray-900 grid grid-cols-[72mm_1fr]">

        {{-- Mørk smaragd sidebar --}}
        <aside class="bg-emerald-950 text-emerald-50 px-8 py-12 flex flex-col gap-9">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-32 h-32 object-cover rounded-full border-4 border-emerald-400 mx-auto">
            @endif

            <section>
                <h2 class="text-[11px] font-bold uppercase tracking-[0.25em] text-emerald-300 border-b border-emerald-700 pb-2">Kontakt</h2>
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

            @if ($skills || $links->isNotEmpty())
                <section>
                    @if ($skills)
                        <h2 class="text-[11px] font-bold uppercase tracking-[0.25em] text-emerald-300 border-b border-emerald-700 pb-2">Kompetencer</h2>
                        <ul class="mt-3 space-y-2 text-sm">
                            @foreach ($skills as $skill)
                                <li class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                                    {{ $skill }}
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($links->isNotEmpty())
                        <h2 class="mt-7 text-[11px] font-bold uppercase tracking-[0.25em] text-emerald-300 border-b border-emerald-700 pb-2">Links</h2>
                        <ul class="mt-3 space-y-2 text-sm">
                            @foreach ($links as $link)
                                <li>
                                    <span class="text-emerald-300">{{ $link->name }}</span>
                                    <a href="{{ $link->url }}" class="block text-emerald-50/80 underline break-all">{{ $link->prettifyUrl() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endif
        </aside>

        {{-- Hovedindhold --}}
        <main class="px-10 py-12">
            <header class="pb-6 border-b-2 border-emerald-900">
                <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-emerald-700">Curriculum Vitae</p>
                <h1 class="mt-2 text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg text-emerald-800 font-medium">{{ $user->job_title }}</p>
                @endif
            </header>

            @if (isset($coverLetter))
                <section class="mt-8">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-emerald-900 border-b border-emerald-200 pb-1">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-8">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-emerald-900 border-b border-emerald-200 pb-1">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="relative pl-5 border-l-2 border-emerald-300">
                                <span class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-emerald-600"></span>
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-semibold text-emerald-700 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm italic text-gray-600">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-1.5 leading-relaxed text-gray-700">{{ $job['description'] }}</p>
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
