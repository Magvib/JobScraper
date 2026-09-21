{{-- Vellum Frame --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fdfcf9] shadow-lg min-h-[297mm] font-sans text-stone-800 p-5">
        {{-- Dobbelt ramme omkring hele siden --}}
        <div class="border border-stone-900 h-full min-h-[287mm]">
            <div class="border border-stone-300 m-1.5 h-full min-h-[273mm] px-12 py-12">

                <header class="text-center pb-8 border-b border-stone-300">
                    @if ($photo)
                        <div class="w-24 h-24 mx-auto rounded-full border border-stone-900 p-1">
                            <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-full">
                        </div>
                    @endif
                    <h1 class="mt-4 font-serif text-4xl font-bold tracking-wide text-stone-900">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-sm font-semibold uppercase tracking-[0.3em] text-stone-500">{{ $user->job_title }}</p>
                    @endif
                    <p class="mt-4 text-xs tracking-wider text-stone-600">
                        {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                    </p>
                </header>

                @if (isset($coverLetter))
                    <section class="mt-8">
                        <h2 class="text-center font-serif text-lg font-bold uppercase tracking-[0.25em] text-stone-900">Ansøgning</h2>
                        <div class="flex items-center justify-center gap-2 mt-2">
                            <span class="h-px w-12 bg-stone-300"></span>
                            <span class="w-1.5 h-1.5 rotate-45 border border-stone-900"></span>
                            <span class="h-px w-12 bg-stone-300"></span>
                        </div>
                        <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-stone-700">
                            {!! $coverLetter->renderContext() !!}
                        </div>
                    </section>
                @elseif ($jobs)
                    <section class="mt-8">
                        <h2 class="text-center font-serif text-lg font-bold uppercase tracking-[0.25em] text-stone-900">Erhvervserfaring &amp; uddannelse</h2>
                        <div class="flex items-center justify-center gap-2 mt-2">
                            <span class="h-px w-12 bg-stone-300"></span>
                            <span class="w-1.5 h-1.5 rotate-45 border border-stone-900"></span>
                            <span class="h-px w-12 bg-stone-300"></span>
                        </div>
                        <div class="mt-7 space-y-6">
                            @foreach ($jobs as $job)
                                <article class="grid grid-cols-[110px_1fr] gap-5">
                                    <p class="text-right text-xs font-semibold tracking-wider text-stone-500 tabular-nums pt-1">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>
                                        – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <div class="border-l border-stone-300 pl-5">
                                        <h3 class="font-serif font-bold text-lg leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                        <p class="text-sm text-stone-500">{{ $job['company'] }}</p>
                                        @if (!empty($job['description']))
                                            <p class="text-sm mt-2 leading-relaxed text-stone-700">{{ $job['description'] }}</p>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($skills)
                    <section class="mt-9">
                        <h2 class="text-center font-serif text-lg font-bold uppercase tracking-[0.25em] text-stone-900">Kompetencer</h2>
                        <div class="flex items-center justify-center gap-2 mt-2">
                            <span class="h-px w-12 bg-stone-300"></span>
                            <span class="w-1.5 h-1.5 rotate-45 border border-stone-900"></span>
                            <span class="h-px w-12 bg-stone-300"></span>
                        </div>
                        <div class="mt-5 flex flex-wrap justify-center gap-2">
                            @foreach ($skills as $skill)
                                <span class="text-[13px] font-medium text-stone-700 border border-stone-400 px-3 py-1">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($links->isNotEmpty())
                    <section class="mt-9">
                        <h2 class="text-center font-serif text-lg font-bold uppercase tracking-[0.25em] text-stone-900">Links</h2>
                        <div class="flex items-center justify-center gap-2 mt-2">
                            <span class="h-px w-12 bg-stone-300"></span>
                            <span class="w-1.5 h-1.5 rotate-45 border border-stone-900"></span>
                            <span class="h-px w-12 bg-stone-300"></span>
                        </div>
                        <ul class="mt-5 space-y-1.5 text-center text-sm text-stone-700">
                            @foreach ($links as $link)
                                <li>
                                    <span class="font-semibold">{{ $link->name }}:</span>
                                    <a href="{{ $link->url }}" class="underline break-all">{{ $link->prettifyUrl() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <footer class="mt-10 text-center text-[10px] uppercase tracking-[0.3em] text-stone-400">
                    @if ($user->birthdate)
                        Født {{ $user->birthdate->format('d/m/Y') }}  ·
                    @endif
                    {{ $user->name }}
                </footer>
            </div>
        </div>
    </div>
</body>
</html>