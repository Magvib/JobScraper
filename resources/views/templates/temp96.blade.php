{{-- Soft Peach --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fff8f3] shadow-md min-h-[297mm] font-sans text-[#5c4033] px-14 py-12">

        <header class="flex items-center gap-8 pb-7 border-b border-[#f0d5c5]">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover rounded-full shrink-0 ring-4 ring-[#ffd9c2]">
            @endif
            <div>
                <h1 class="text-4xl font-bold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1 text-lg text-[#d08c6a]">{{ $user->job_title }}</p>
                @endif
                <p class="mt-2.5 text-sm text-[#a3806d]">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </p>
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-[#d08c6a]">Ansøgning</h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-[#d08c6a]">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="flex gap-6 items-start">
                            <div class="shrink-0 text-center">
                                <p class="text-2xl font-bold text-[#e8a87c] tabular-nums">{{ \Carbon\Carbon::parse($job['startDate'])->format('Y') }}</p>
                                <div class="mx-auto mt-1 w-px h-8 bg-[#f0d5c5]"></div>
                            </div>
                            <div class="flex-1 pb-1">
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-semibold text-[#c9a08a] whitespace-nowrap tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-medium text-[#d08c6a]">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-[#75594a]">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-[#d08c6a]">Kompetencer</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-sm text-[#75594a] bg-[#ffead9] px-3.5 py-1.5 rounded-full">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($user->birthdate)
            <p class="mt-9 text-xs text-[#c9a08a]">Fødselsdato: {{ $user->birthdate->format('d/m/Y') }}</p>
        @endif
    </div>
</body>
</html>
