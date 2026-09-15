{{-- Onyx Pro --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-xl min-h-[297mm] font-sans text-neutral-900">

        {{-- Sort premium header med guldstreg --}}
        <header class="bg-neutral-950 text-white px-12 py-12 relative">
            <div class="flex items-center justify-between gap-8">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.5em] text-amber-400">Curriculum Vitae</p>
                    <h1 class="mt-3 text-5xl font-black tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-lg text-neutral-400">{{ $user->job_title }}</p>
                    @endif
                    <div class="mt-5 h-0.5 w-32 bg-amber-400"></div>
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-28 h-28 object-cover shrink-0 border-2 border-amber-400 p-1">
                @endif
            </div>
            <div class="mt-6 flex flex-wrap gap-x-6 gap-y-1 text-sm text-neutral-300">
                @if ($user->phone)
                    <span><span class="text-amber-400">✆</span> {{ $user->phone }}</span>
                @endif
                <span class="break-all"><span class="text-amber-400">✉</span> {{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span><span class="text-amber-400">◎</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
            </div>
        </header>

        <div class="px-12 py-10">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-sm font-black uppercase tracking-[0.3em] flex items-center gap-4">
                        Ansøgning <span class="flex-1 h-px bg-neutral-300"></span> <span class="w-2 h-2 bg-amber-400 rotate-45"></span>
                    </h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-sm font-black uppercase tracking-[0.3em] flex items-center gap-4">
                        Erfaring &amp; uddannelse <span class="flex-1 h-px bg-neutral-300"></span> <span class="w-2 h-2 bg-amber-400 rotate-45"></span>
                    </h2>
                    <div class="mt-7 space-y-7">
                        @foreach ($jobs as $job)
                            <article class="flex gap-6 items-start">
                                <div class="shrink-0 w-1 self-stretch bg-gradient-to-b from-amber-400 to-neutral-200 rounded-full"></div>
                                <div class="flex-1">
                                    <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                        <h3 class="text-xl font-extrabold tracking-tight leading-snug">{{ $job['title'] }}</h3>
                                        <p class="text-xs font-bold text-neutral-500 whitespace-nowrap tabular-nums border border-neutral-300 px-2 py-0.5">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                            –
                                            {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                    </div>
                                    <p class="text-sm font-semibold text-neutral-500 uppercase tracking-widest mt-0.5">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2.5 leading-relaxed text-neutral-600">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="text-sm font-black uppercase tracking-[0.3em] flex items-center gap-4">
                        Kompetencer <span class="flex-1 h-px bg-neutral-300"></span> <span class="w-2 h-2 bg-amber-400 rotate-45"></span>
                    </h2>
                    <div class="mt-5 grid grid-cols-3 gap-2.5">
                        @foreach ($skills as $skill)
                            <p class="text-xs font-bold uppercase tracking-wider text-center bg-neutral-950 text-white px-2 py-2.5">{{ $skill }}</p>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($user->birthdate)
                <p class="mt-10 text-xs text-neutral-400">Fødselsdato: {{ $user->birthdate->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>
</body>
</html>
