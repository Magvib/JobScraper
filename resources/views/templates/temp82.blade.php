{{-- Icy Minimal --}}
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
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-slate-800 px-18 py-16" style="padding-left:5rem;padding-right:5rem;">

        {{-- Næsten usynlig elegance --}}
        <header>
            <div class="flex items-start justify-between gap-8">
                <div>
                    <h1 class="text-3xl font-light tracking-[0.2em] uppercase">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-xs tracking-[0.3em] uppercase text-slate-400">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-16 h-16 object-cover rounded-full grayscale opacity-90">
                @endif
            </div>
            <p class="mt-5 text-xs text-slate-400 tracking-widest uppercase">
                {{ collect([$user->phone, $user->email, $user->city ? trim(($user->zip ?? '') . ' ' . $user->city) : null])->filter()->implode('    ') }}
            </p>
            <div class="mt-6 h-px bg-slate-100"></div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-12">
                <h2 class="text-[10px] tracking-[0.5em] uppercase text-slate-300">Ansøgning</h2>
                <div class="mt-6 space-y-5 leading-loose text-[15px] text-slate-600">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-12">
                <h2 class="text-[10px] tracking-[0.5em] uppercase text-slate-300">Erfaring &amp; uddannelse</h2>
                <div class="mt-8 space-y-9">
                    @foreach ($jobs as $job)
                        <article>
                            <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                <h3 class="text-base font-medium">{{ $job['title'] }}</h3>
                                <p class="text-[10px] tracking-[0.25em] uppercase text-slate-300 tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="mt-0.5 text-xs tracking-widest uppercase text-slate-400">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2.5 text-sm leading-loose text-slate-500">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-12">
                <h2 class="text-[10px] tracking-[0.5em] uppercase text-slate-300">Kompetencer</h2>
                <p class="mt-4 text-xs tracking-[0.2em] uppercase text-slate-400 leading-loose">{{ implode('    ·    ', $skills) }}</p>
            </section>
        @endif

        @if ($user->birthdate)
            <p class="mt-14 text-[10px] tracking-[0.4em] uppercase text-slate-300">Født {{ $user->birthdate->format('d/m/Y') }}</p>
        @endif
    </div>
</body>
</html>
