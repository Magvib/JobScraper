{{-- Arch Frame --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#f3f1ea] shadow-xl min-h-[297mm] font-sans text-stone-800 overflow-hidden">

        {{-- Buevindue med foto --}}
        <header class="flex items-end justify-between gap-10 px-14 pt-14 pb-10">
            <div class="flex-1 pb-2">
                <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-stone-400">Curriculum Vitae</p>
                <h1 class="mt-4 text-5xl font-extrabold tracking-tight text-stone-900 leading-none">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-3 text-lg font-semibold text-green-800">{{ $user->job_title }}</p>
                @endif
                <p class="mt-6 text-sm text-stone-500 leading-relaxed">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
                </p>
            </div>
            @if ($photo)
                <div class="shrink-0 w-36 h-48 rounded-t-full bg-white border border-stone-300 p-2 shadow-sm">
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-t-full">
                </div>
            @endif
        </header>

        <div class="bg-white border-y border-stone-200 px-14 py-10">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-green-900 border-b border-stone-200 pb-2">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-green-900 border-b border-stone-200 pb-2">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-6">
                                <div class="shrink-0 w-2 h-14 rounded-t-full rounded-b-sm bg-green-800/80"></div>
                                <div>
                                    <p class="text-[11px] font-bold tracking-widest text-green-700 tabular-nums uppercase">
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
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-green-900 border-b border-stone-200 pb-2">Kompetencer</h2>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        @foreach ($skills as $skill)
                            <span class="text-[13px] font-semibold text-stone-800 bg-[#f3f1ea] border border-stone-300 rounded-full px-4 py-1.5">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-9">
                    <h2 class="text-xs font-extrabold uppercase tracking-[0.35em] text-green-900 border-b border-stone-200 pb-2">Links</h2>
                    <ul class="mt-5 text-sm space-y-1.5 text-stone-700">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold text-stone-900">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-green-800 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <footer class="px-14 py-7 flex justify-between text-xs text-stone-400 tracking-wide uppercase">
            <span>{{ $user->name }}</span>
            <span>{{ now()->format('Y') }}</span>
        </footer>
    </div>
</body>
</html>