{{-- Ivory Plain --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md px-16 py-14 font-sans text-gray-900 min-h-[297mm]">

        {{-- Simpel sort/hvid header --}}
        <header class="pb-5 border-b border-gray-900">
            <div class="flex items-baseline justify-between gap-6 flex-wrap">
                <h1 class="text-3xl font-bold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="text-base text-gray-600">{{ $user->job_title }}</p>
                @endif
            </div>
            <p class="mt-2 text-sm text-gray-600">
                {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? '')), $user->phone, $user->email])->filter()->implode(' · ') }}
            </p>
        </header>

        @if (isset($coverLetter))
            <section class="mt-7">
                <h2 class="text-xs font-bold uppercase tracking-widest text-gray-900">Ansøgning</h2>
                <div class="mt-4 space-y-4 text-[15px] leading-relaxed">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-7">
                <h2 class="text-xs font-bold uppercase tracking-widest text-gray-900">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-4 space-y-5">
                    @foreach ($jobs as $job)
                        <article>
                            <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                <h3 class="font-semibold leading-snug">{{ $job['title'] }}<span class="font-normal text-gray-600">, {{ $job['company'] }}</span></h3>
                                <p class="text-sm text-gray-500 whitespace-nowrap tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-1 leading-relaxed text-gray-700">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-7">
                <h2 class="text-xs font-bold uppercase tracking-widest text-gray-900">Kompetencer</h2>
                <p class="mt-2 text-sm text-gray-700">{{ implode(', ', $skills) }}</p>
            </section>
        @endif

        <section class="mt-7 pt-4 border-t border-gray-300">
            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-900">Personlige oplysninger</h2>
            <p class="mt-2 text-sm text-gray-700">
                {{ collect([$user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null, collect([$user->address, $user->zip, $user->city])->filter()->implode(', '), $user->phone, $user->email])->filter()->implode(' · ') }}
            </p>
        </section>
    </div>
</body>
</html>
