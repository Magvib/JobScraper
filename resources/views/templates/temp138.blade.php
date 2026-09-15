{{-- Binder Rings --}}
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
        /* Linjeret papir */}
        .lined {
            background-image: repeating-linear-gradient(
                to bottom,
                transparent 0,
                transparent 35px,
                #bfdbfe 35px,
                #bfdbfe 36px
            );
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white lined shadow-lg min-h-[297mm] font-sans text-slate-800 relative overflow-hidden">

        {{-- Tre ringbindsringe ned ad venstre kant --}}
        <div class="absolute left-10 top-24 bottom-24 flex flex-col justify-between items-center z-10">
            <div class="relative w-7 h-7">
                <div class="absolute inset-0 rounded-full border-4 border-slate-400 bg-transparent"></div>
                <div class="absolute inset-1 rounded-full bg-white"></div>
            </div>
            <div class="relative w-7 h-7">
                <div class="absolute inset-0 rounded-full border-4 border-slate-400 bg-transparent"></div>
                <div class="absolute inset-1 rounded-full bg-white"></div>
            </div>
            <div class="relative w-7 h-7">
                <div class="absolute inset-0 rounded-full border-4 border-slate-400 bg-transparent"></div>
                <div class="absolute inset-1 rounded-full bg-white"></div>
            </div>
        </div>

        <div class="pl-28 pr-14 py-14">
            <header class="flex items-start justify-between gap-8 pb-7">
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 leading-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-base font-semibold text-blue-700">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover shrink-0 rounded-full border-4 border-blue-200">
                @endif
            </header>
            <p class="text-sm text-slate-500 leading-9 pb-4">
                {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
            </p>

            @if (isset($coverLetter))
                <section class="mt-6">
                    <h2 class="inline-block text-xs font-extrabold uppercase tracking-widest text-white bg-blue-700 px-4 py-2 rounded-r-md">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-9 text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-6">
                    <h2 class="inline-block text-xs font-extrabold uppercase tracking-widest text-white bg-blue-700 px-4 py-2 rounded-r-md">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[100px_1fr] gap-5">
                                <p class="text-right text-xs font-bold text-slate-400 tabular-nums pt-2 uppercase tracking-wider">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <div class="border-l-4 border-blue-200 pl-5">
                                    <h3 class="font-bold text-lg leading-9 text-slate-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm leading-9 text-slate-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm leading-9 text-slate-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="inline-block text-xs font-extrabold uppercase tracking-widest text-white bg-blue-700 px-4 py-2 rounded-r-md">Kompetencer</h2>
                    <p class="mt-5 text-sm leading-9 font-medium text-slate-700">
                        {{ implode('   ·   ', $skills) }}
                    </p>
                </section>
            @endif

            <footer class="mt-12 text-xs leading-9 text-slate-400">
                @if ($user->birthdate)
                    Født {{ $user->birthdate->format('d/m/Y') }}  ·
                @endif
                {{ $user->name }}
            </footer>
        </div>
    </div>
</body>
</html>