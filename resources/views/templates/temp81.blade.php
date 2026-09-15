{{-- Clay Studio --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#efe9e2] shadow-lg min-h-[297mm] font-sans text-[#43302b]">

        {{-- Asymmetrisk ler-blok header --}}
        <header class="px-12 pt-12">
            <div class="bg-[#b45b4d] text-white rounded-t-[3rem] rounded-br-[3rem] px-10 py-9 flex items-center gap-7">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 object-cover rounded-[2rem] shrink-0 border-4 border-[#e8c7bf]">
                @endif
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-[#f4d6cd] text-lg">{{ $user->job_title }}</p>
                    @endif
                </div>
            </div>
            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-[#7a5c55] px-2">
                @if ($user->phone)
                    <span>{{ $user->phone }}</span>
                @endif
                <span class="break-all">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>
        </header>

        <div class="px-12 py-8">
            @if (isset($coverLetter))
                <section class="bg-white rounded-[2rem] px-8 py-7 shadow-sm">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#b45b4d]">Ansøgning</h2>
                    <div class="mt-4 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#b45b4d] px-1">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-4 space-y-4">
                        @foreach ($jobs as $job)
                            <article class="bg-white rounded-[1.5rem] px-7 py-5 shadow-sm flex gap-6 items-start {{ $loop->odd ? 'rounded-tl-none' : 'rounded-tr-none' }}">
                                <p class="shrink-0 text-xs font-bold text-[#b45b4d] bg-[#f8e3de] rounded-full px-3 py-1.5 tabular-nums whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <div>
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm font-medium text-[#96655b]">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-[#5c433d]">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-8">
                    <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#b45b4d] px-1">Kompetencer</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-semibold text-[#43302b] bg-white shadow-sm px-4 py-1.5 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</body>
</html>
