{{-- Sunset Horizon --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-stone-900">

        {{-- Solnedgang header --}}
        <header class="bg-gradient-to-r from-orange-500 via-rose-500 to-pink-600 text-white px-12 py-11">
            <div class="flex items-center justify-between gap-8">
                <div>
                    <h1 class="text-5xl font-black tracking-tight drop-shadow-sm">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-xl text-orange-100">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 object-cover rounded-full shrink-0 ring-4 ring-white/50">
                @endif
            </div>
            <div class="mt-5 flex flex-wrap gap-x-5 gap-y-1 text-sm text-orange-50">
                @if ($user->phone)
                    <span>{{ $user->phone }}</span>
                @endif
                <span class="break-all">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
            </div>
        </header>

        <div class="px-12 py-9">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-rose-600">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-rose-600">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-5 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5 items-start">
                                <span class="shrink-0 mt-1 w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-rose-500 text-white flex items-center justify-center text-xs font-black tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('y') }}
                                </span>
                                <div class="flex-1 border-b border-orange-100 pb-5">
                                    <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                        <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                        <p class="text-xs font-bold text-rose-600 whitespace-nowrap tabular-nums">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                            –
                                            {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                    </div>
                                    <p class="text-sm font-medium text-stone-500">{{ $job['company'] }}</p>
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
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-rose-600">Kompetencer</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-semibold text-rose-700 bg-rose-50 px-3.5 py-1.5 rounded-full border border-rose-200">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-9 pt-4 border-t border-orange-100 flex justify-between text-xs text-stone-500">
                <span>{{ $user->name }}</span>
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>
