{{-- Dot Leader --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#faf8f2] shadow-lg min-h-[297mm] font-serif text-stone-900 px-14 py-14 overflow-hidden">

        {{-- Menu-kort elegance --}}
        <header class="text-center pb-8 border-b border-stone-300">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover rounded-full mx-auto border border-stone-400 p-1">
            @endif
            <h1 class="mt-4 text-4xl font-bold tracking-wide">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 text-base italic text-stone-500">{{ $user->job_title }}</p>
            @endif
            <p class="mt-4 font-sans text-xs uppercase tracking-[0.25em] text-stone-500">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
            </p>
        </header>

        @if (isset($coverLetter))
            <section class="mt-10">
                <h2 class="text-center text-sm font-bold uppercase tracking-[0.35em] text-stone-700">Ansøgning</h2>
                <div class="mt-6 space-y-4 text-[15px] leading-loose text-stone-800">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-10">
                <h2 class="text-center text-sm font-bold uppercase tracking-[0.35em] text-stone-700">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-8 space-y-7">
                    @foreach ($jobs as $job)
                        <article>
                            <div class="flex items-baseline gap-3">
                                <h3 class="text-lg font-bold whitespace-nowrap">{{ $job['title'] }}</h3>
                                <span class="flex-1 border-b-2 border-dotted border-stone-300 -translate-y-1"></span>
                                <p class="text-xs font-sans font-bold tabular-nums text-stone-500 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="mt-1 text-sm italic text-stone-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-stone-700 text-justify">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-11">
                <h2 class="text-center text-sm font-bold uppercase tracking-[0.35em] text-stone-700">Kompetencer</h2>
                <div class="mt-6 space-y-2.5">
                    @foreach ($skills as $skill)
                        <p class="flex items-baseline gap-3 text-sm font-medium">
                            {{ $skill }}
                            <span class="flex-1 border-b-2 border-dotted border-stone-300 -translate-y-1"></span>
                        </p>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-12 pt-5 border-t border-stone-300 text-center font-sans text-[10px] uppercase tracking-[0.35em] text-stone-400">
            {{ $user->name }} — Curriculum Vitae
        </footer>
    </div>
</body>
</html>