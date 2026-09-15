{{-- Sand Dune --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-stone-100 shadow-lg min-h-[297mm] font-sans text-stone-800">

        {{-- Sand header med bue-formet foto --}}
        <header class="bg-gradient-to-b from-stone-300 to-stone-100 px-12 pt-10 pb-8 text-center">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-32 object-cover mx-auto rounded-t-full border-4 border-white shadow-md">
            @endif
            <h1 class="mt-4 text-4xl font-bold tracking-tight">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-1.5 text-stone-600 text-lg">{{ $user->job_title }}</p>
            @endif
            <div class="mt-4 inline-flex flex-wrap justify-center gap-x-5 gap-y-1 text-sm text-stone-600 bg-white/70 rounded-full px-6 py-2">
                @if ($user->phone)
                    <span>{{ $user->phone }}</span>
                @endif
                <span class="break-all">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
            </div>
        </header>

        <div class="px-12 pb-10">
            @if (isset($coverLetter))
                <section class="mt-6">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-stone-500 text-center">— Ansøgning —</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-6">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-stone-500 text-center">— Erhvervserfaring &amp; uddannelse —</h2>
                    <div class="mt-6 grid gap-4">
                        @foreach ($jobs as $job)
                            <article class="bg-white rounded-xl px-7 py-5 shadow-sm">
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold text-stone-500 whitespace-nowrap tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
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
                <section class="mt-8">
                    <h2 class="text-sm font-bold uppercase tracking-widest text-stone-500 text-center">— Kompetencer —</h2>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-medium text-stone-700 bg-stone-200/80 px-3.5 py-1.5 rounded-lg">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($user->birthdate)
                <p class="mt-8 text-center text-xs text-stone-500">Fødselsdato: {{ $user->birthdate->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>
</body>
</html>
