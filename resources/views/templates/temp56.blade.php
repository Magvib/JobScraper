{{-- Forest Path --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#f7f9f4] shadow-lg min-h-[297mm] font-sans text-green-950 px-12 py-12">

        {{-- Header med rundet grønt kort --}}
        <header class="bg-gradient-to-br from-green-800 to-emerald-700 text-white rounded-3xl px-9 py-8 flex items-center gap-7 shadow-md">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover rounded-2xl shrink-0 border-2 border-emerald-300">
            @endif
            <div class="flex-1">
                <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-emerald-200 text-lg">{{ $user->job_title }}</p>
                @endif
                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-emerald-100">
                    @if ($user->phone)
                        <span>{{ $user->phone }}</span>
                    @endif
                    <span class="break-all">{{ $user->email }}</span>
                    @if ($user->address || $user->city)
                        <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                    @endif
                </div>
            </div>
        </header>

        <div class="mt-9">
            @if (isset($coverLetter))
                <section class="bg-white rounded-3xl shadow-sm border border-green-100 px-9 py-7">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-green-800">Ansøgning</h2>
                    <div class="mt-4 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-green-800 px-2">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-4 space-y-4">
                        @foreach ($jobs as $job)
                            <article class="bg-white rounded-2xl shadow-sm border border-green-100 px-7 py-5 flex gap-6 items-start">
                                <div class="shrink-0 bg-green-50 border border-green-200 rounded-xl px-3 py-2 text-center">
                                    <p class="text-sm font-black text-green-800 tabular-nums">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}</p>
                                    <p class="text-[10px] font-bold uppercase text-green-600">til</p>
                                    <p class="text-sm font-black text-green-800 tabular-nums">{{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}</p>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-emerald-700">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-green-950/75">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-8">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-green-800 px-2">Kompetencer</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-semibold text-green-900 bg-white border-2 border-green-300 px-3.5 py-1.5 rounded-xl">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($user->birthdate)
                <p class="mt-8 text-xs text-green-900/60 px-2">Fødselsdato: {{ $user->birthdate->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>
</body>
</html>
