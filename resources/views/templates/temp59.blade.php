{{-- Mocha Warm --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-amber-50/60 shadow-md min-h-[297mm] font-serif text-[#3f3226] px-14 py-12">

        {{-- Varm header --}}
        <header class="flex items-start justify-between gap-8 pb-6 border-b-4 border-double border-amber-900/60">
            <div>
                <h1 class="text-4xl font-bold">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-lg italic text-amber-900/80">{{ $user->job_title }}</p>
                @endif
                <ul class="mt-4 space-y-1 text-sm text-[#5c4d3c]">
                    @if ($user->phone)
                        <li>☎ {{ $user->phone }}</li>
                    @endif
                    <li class="break-all">✉ {{ $user->email }}</li>
                    @if ($user->address || $user->city)
                        <li>⌂ {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</li>
                    @endif
                </ul>
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-36 object-cover shrink-0 rounded shadow-md rotate-2 border-4 border-white">
            @endif
        </header>

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="text-xl font-bold text-amber-950 border-l-8 border-amber-800/70 pl-4">Ansøgning</h2>
                <div class="mt-5 space-y-4 leading-relaxed text-[15px]">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="text-xl font-bold text-amber-950 border-l-8 border-amber-800/70 pl-4">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="bg-white/70 rounded-lg px-6 py-5 shadow-sm">
                            <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-xs font-bold text-amber-800 whitespace-nowrap tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-sm italic text-[#6b5844]">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-xl font-bold text-amber-950 border-l-8 border-amber-800/70 pl-4">Kompetencer</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-sm font-medium text-amber-950 bg-amber-100 px-3.5 py-1.5 rounded border border-amber-300/60">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-10 pt-4 border-t-2 border-amber-900/40 flex justify-between text-xs text-[#7a6750]">
            <span>{{ $user->name }}</span>
            @if ($user->birthdate)
                <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </footer>
    </div>
</body>
</html>
