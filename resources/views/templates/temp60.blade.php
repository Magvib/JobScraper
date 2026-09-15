{{-- Aurora Modern --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-slate-50 shadow-xl min-h-[297mm] font-sans text-slate-900 overflow-hidden">

        {{-- Aurora gradient header --}}
        <header class="relative bg-gradient-to-br from-violet-700 via-fuchsia-600 to-cyan-500 px-12 pt-14 pb-20 text-white">
            <div class="absolute top-6 right-10 w-24 h-24 rounded-full bg-white/10 blur-xl"></div>
            <div class="absolute bottom-8 left-16 w-16 h-16 rounded-full bg-cyan-300/20 blur-lg"></div>
            <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-white/70">Curriculum Vitae</p>
            <h1 class="mt-3 text-5xl font-black tracking-tight">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 text-xl text-white/85 font-light">{{ $user->job_title }}</p>
            @endif
        </header>

        {{-- Overlappende kontaktkort --}}
        <div class="px-12 -mt-10 relative z-10">
            <div class="bg-white rounded-2xl shadow-lg px-7 py-5 flex items-center gap-6">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-16 h-16 object-cover rounded-full shrink-0 ring-4 ring-violet-100">
                @endif
                <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm text-slate-600">
                    @if ($user->phone)
                        <span><span class="font-bold text-violet-700">Tlf:</span> {{ $user->phone }}</span>
                    @endif
                    <span class="break-all"><span class="font-bold text-violet-700">Mail:</span> {{ $user->email }}</span>
                    @if ($user->address || $user->city)
                        <span><span class="font-bold text-violet-700">Adr:</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                    @endif
                    @if ($user->birthdate)
                        <span><span class="font-bold text-violet-700">Født:</span> {{ $user->birthdate->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="px-12 py-9">
            @if (isset($coverLetter))
                <section class="bg-white rounded-2xl shadow-sm p-8">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest bg-gradient-to-r from-violet-700 to-cyan-600 bg-clip-text text-transparent">Ansøgning</h2>
                    <div class="mt-4 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-sm font-extrabold uppercase tracking-widest bg-gradient-to-r from-violet-700 to-cyan-600 bg-clip-text text-transparent">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-5 space-y-4">
                        @foreach ($jobs as $job)
                            <article class="bg-white rounded-2xl shadow-sm px-7 py-5 border-l-4 border-fuchsia-500">
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold text-white bg-gradient-to-r from-violet-600 to-fuchsia-500 px-3 py-1 rounded-full whitespace-nowrap tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-violet-700">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest bg-gradient-to-r from-violet-700 to-cyan-600 bg-clip-text text-transparent">Kompetencer</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-semibold text-violet-800 bg-white shadow-sm px-3.5 py-1.5 rounded-full border border-violet-200">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</body>
</html>
