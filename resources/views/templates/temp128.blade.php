{{-- Checklist --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-mono text-stone-800 px-14 py-12">

        {{-- Checklist-header med afkrydsede felter --}}
        <header class="flex items-start justify-between gap-8 pb-8 border-b-2 border-stone-900">
            <div>
                <h1 class="text-4xl font-bold tracking-tight text-stone-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-base text-stone-600">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover shrink-0 border-2 border-stone-900">
            @endif
        </header>

        <div class="mt-5 space-y-1.5 text-sm text-stone-600">
            <p><span class="inline-block w-4.5 h-4.5 border-2 border-stone-900 text-green-700 text-xs font-bold leading-4 text-center align-middle mr-2.5">✓</span>{{ $user->phone ?? '—' }}</p>
            <p class="break-all"><span class="inline-block w-4.5 h-4.5 border-2 border-stone-900 text-green-700 text-xs font-bold leading-4 text-center align-middle mr-2.5">✓</span>{{ $user->email }}</p>
            @if ($user->address || $user->city)
                <p><span class="inline-block w-4.5 h-4.5 border-2 border-stone-900 text-green-700 text-xs font-bold leading-4 text-center align-middle mr-2.5">✓</span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
            @endif
            @if ($user->birthdate)
                <p><span class="inline-block w-4.5 h-4.5 border-2 border-stone-900 text-green-700 text-xs font-bold leading-4 text-center align-middle mr-2.5">✓</span>Født {{ $user->birthdate->format('d/m/Y') }}</p>
            @endif
        </div>

        @if (isset($coverLetter))
            <section class="mt-9">
                <h2 class="text-sm font-bold bg-stone-900 text-white inline-block px-3 py-1.5">[ ] ANSØGNING</h2>
                <div class="mt-5 space-y-4 text-sm leading-relaxed text-stone-700">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-9">
                <h2 class="text-sm font-bold bg-stone-900 text-white inline-block px-3 py-1.5">[ ] ERHVERVSERFARING &amp; UDDANNELSE</h2>
                <div class="mt-6 space-y-5">
                    @foreach ($jobs as $job)
                        <article class="border-2 border-stone-900 px-5 py-4 shadow-[4px_4px_0_0_#1c1917]">
                            <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                <h3 class="font-bold">{{ $job['title'] }}</h3>
                                <p class="text-xs font-bold text-stone-500 tabular-nums whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-xs text-stone-500">@ {{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-sm font-bold bg-stone-900 text-white inline-block px-3 py-1.5">[ ] KOMPETENCER</h2>
                <div class="mt-5 grid grid-cols-2 gap-x-8 gap-y-2">
                    @foreach ($skills as $skill)
                        <p class="flex items-center gap-2.5 text-sm">
                            <span class="inline-block w-4.5 h-4.5 shrink-0 border-2 border-stone-900 text-green-700 text-xs font-bold leading-4 text-center">✓</span>
                            {{ $skill }}
                        </p>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-12 pt-4 border-t-2 border-dashed border-stone-300 flex justify-between text-xs text-stone-400">
            <span>{{ $user->name }}</span>
            <span>✓ verified</span>
        </footer>
    </div>
</body>
</html>