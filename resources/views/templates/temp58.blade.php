{{-- Azure Index --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-blue-950 px-12 py-12">

        {{-- Struktureret indeks-header --}}
        <header class="border-2 border-blue-900">
            <div class="bg-blue-900 text-white px-8 py-6 flex items-center justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1 text-blue-200 text-lg">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-20 h-20 object-cover shrink-0 border-2 border-blue-300">
                @endif
            </div>
            <div class="grid grid-cols-3 divide-x divide-blue-200 text-center text-sm">
                <p class="px-4 py-2.5">{{ $user->phone ?? '—' }}</p>
                <p class="px-4 py-2.5 break-all">{{ $user->email }}</p>
                <p class="px-4 py-2.5">{{ collect([$user->zip, $user->city])->filter()->implode(' ') ?: '—' }}</p>
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="text-xs font-black uppercase tracking-[0.25em] text-blue-900 bg-blue-50 border-l-4 border-blue-900 px-3 py-2">Ansøgning</h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="text-xs font-black uppercase tracking-[0.25em] text-blue-900 bg-blue-50 border-l-4 border-blue-900 px-3 py-2">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-5 border border-blue-200 divide-y divide-blue-100">
                    @foreach ($jobs as $job)
                        <article class="grid grid-cols-[120px_1fr] gap-5 px-5 py-4 hover:bg-blue-50/40">
                            <div class="text-xs font-bold text-blue-800 pt-0.5 tabular-nums">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                <br>–<br>
                                {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </div>
                            <div>
                                <h3 class="font-bold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm text-blue-700 font-medium">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-1.5 leading-relaxed text-blue-950/75">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-8">
                <h2 class="text-xs font-black uppercase tracking-[0.25em] text-blue-900 bg-blue-50 border-l-4 border-blue-900 px-3 py-2">Kompetencer</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-900 border border-blue-900 px-2.5 py-1">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-8">
                <h2 class="text-xs font-black uppercase tracking-[0.25em] text-blue-900 bg-blue-50 border-l-4 border-blue-900 px-3 py-2">Links</h2>
                <ul class="mt-4 border border-blue-200 divide-y divide-blue-100">
                    @foreach ($links as $link)
                        <li class="px-5 py-3 text-sm flex flex-wrap items-baseline gap-x-2">
                            <span class="font-bold text-blue-900">{{ $link->name }}</span>
                            <a href="{{ $link->url }}" class="text-blue-700 underline break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-10 text-center text-[10px] uppercase tracking-[0.3em] text-blue-900/50">
            {{ collect([$user->address, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
        </footer>
    </div>
</body>
</html>
