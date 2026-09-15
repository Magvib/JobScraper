{{-- Crimson Corporate --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ $user->name }}</title>
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-gray-900">

        {{-- To-delt header: rød blok + grå blok --}}
        <header>
            <div class="bg-red-700 text-white px-12 pt-11 pb-7 flex items-end justify-between gap-8">
                <div>
                    <h1 class="text-5xl font-black tracking-tight leading-none">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-3 text-lg text-red-100">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-32 object-cover shrink-0 border-4 border-white shadow-md">
                @endif
            </div>
            <div class="bg-gray-800 text-gray-200 px-12 py-2.5 flex flex-wrap gap-x-6 gap-y-1 text-xs uppercase tracking-wider">
                @if ($user->phone)
                    <span>{{ $user->phone }}</span>
                @endif
                <span class="break-all normal-case tracking-normal">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span>{{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>
        </header>

        <div class="px-12 py-10">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-base font-extrabold text-red-700 uppercase tracking-wide flex items-center gap-3">
                        Ansøgning <span class="flex-1 h-0.5 bg-red-700"></span>
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-base font-extrabold text-red-700 uppercase tracking-wide flex items-center gap-3">
                        Erhvervserfaring &amp; uddannelse <span class="flex-1 h-0.5 bg-red-700"></span>
                    </h2>
                    <div class="mt-6 border-l-4 border-gray-800 pl-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article>
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold text-white bg-red-700 px-2.5 py-1 whitespace-nowrap tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-gray-700">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="text-base font-extrabold text-red-700 uppercase tracking-wide flex items-center gap-3">
                        Kompetencer <span class="flex-1 h-0.5 bg-red-700"></span>
                    </h2>
                    <div class="mt-4 grid grid-cols-2 gap-x-8 gap-y-1.5">
                        @foreach ($skills as $skill)
                            <p class="text-sm flex items-center gap-2">
                                <span class="w-2 h-2 bg-red-700 shrink-0"></span>{{ $skill }}
                            </p>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</body>
</html>
