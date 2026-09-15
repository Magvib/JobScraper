{{-- Teal Ribbon --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-gray-900 relative">

        {{-- Teal ribbon i venstre kant --}}
        <div class="absolute left-0 top-0 bottom-0 w-3 bg-teal-600"></div>

        <div class="pl-14 pr-12 py-12">
            <header class="flex items-center gap-6">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 object-cover shrink-0 rounded-full ring-4 ring-teal-100">
                @endif
                <div class="flex-1">
                    <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1 text-lg text-teal-700 font-medium">{{ $user->job_title }}</p>
                    @endif
                </div>
            </header>

            {{-- Kontakt-bånd --}}
            <div class="mt-6 bg-teal-600 text-white -ml-2 pl-6 pr-4 py-2.5 flex flex-wrap gap-x-6 gap-y-1 text-sm rounded-r-full">
                @if ($user->phone)
                    <span>{{ $user->phone }}</span>
                @endif
                <span class="break-all">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span>{{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>

            @if (isset($coverLetter))
                <section class="mt-10">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-teal-700">
                        <span class="border-b-2 border-teal-600 pb-1">Ansøgning</span>
                    </h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-10">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-teal-700">
                        <span class="border-b-2 border-teal-600 pb-1">Erhvervserfaring &amp; uddannelse</span>
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5">
                                <div class="shrink-0 w-20 text-right">
                                    <p class="text-sm font-extrabold text-teal-700">{{ \Carbon\Carbon::parse($job['startDate'])->format('Y') }}</p>
                                    <p class="text-xs text-gray-500">
                                        – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('Y') : 'nu' }}
                                    </p>
                                </div>
                                <div class="flex-1 border-l-2 border-teal-200 pl-5 pb-1">
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-gray-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-gray-700">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-teal-700">
                        <span class="border-b-2 border-teal-600 pb-1">Kompetencer</span>
                    </h2>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-semibold text-teal-800 border-2 border-teal-600 px-3 py-1 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</body>
</html>
