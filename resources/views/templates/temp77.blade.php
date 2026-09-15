{{-- Cobalt Bold --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-blue-950">

        {{-- Diagonal kobolt header --}}
        <header class="relative bg-blue-700 text-white px-12 pt-12 pb-14" style="clip-path: polygon(0 0, 100% 0, 100% 82%, 0 100%);">
            <div class="flex items-center gap-7">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 object-cover rounded-xl shrink-0 border-4 border-white/70 shadow-lg">
                @endif
                <div>
                    <h1 class="text-5xl font-black tracking-tight leading-none">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-xl text-blue-200">{{ $user->job_title }}</p>
                    @endif
                </div>
            </div>
        </header>

        <div class="px-12 py-4 -mt-5 relative">
            <div class="bg-white shadow-lg border border-blue-100 rounded-xl px-6 py-4 flex flex-wrap gap-x-8 gap-y-1 text-sm text-blue-900">
                @if ($user->phone)
                    <span class="font-semibold">{{ $user->phone }}</span>
                @endif
                <span class="break-all font-semibold">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span class="font-semibold">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span class="font-semibold">{{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>
        </div>

        <div class="px-12 py-8">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-xl font-black text-blue-700 uppercase tracking-tight">Ansøgning</h2>
                    <div class="mt-1 h-1.5 w-16 bg-blue-700 rounded-full"></div>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-xl font-black text-blue-700 uppercase tracking-tight">Erfaring &amp; uddannelse</h2>
                    <div class="mt-1 h-1.5 w-16 bg-blue-700 rounded-full"></div>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article>
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-xl font-extrabold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-black text-blue-700 whitespace-nowrap tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        —
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-bold text-blue-400 uppercase tracking-wide">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-blue-950/75">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-xl font-black text-blue-700 uppercase tracking-tight">Kompetencer</h2>
                    <div class="mt-1 h-1.5 w-16 bg-blue-700 rounded-full"></div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-bold text-white bg-blue-700 px-4 py-1.5 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</body>
</html>
