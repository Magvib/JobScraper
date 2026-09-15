{{-- Retro Orange --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fdf6ec] shadow-lg min-h-[297mm] font-sans text-[#3d2b1f] overflow-hidden">

        {{-- 70'er striber --}}
        <div class="h-2.5 bg-orange-500"></div>
        <div class="h-2.5 bg-amber-400"></div>
        <div class="h-2.5 bg-[#a0522d]"></div>

        <header class="px-12 pt-10 flex items-center justify-between gap-8">
            <div>
                <h1 class="text-5xl font-black tracking-tight text-orange-600">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-xl font-bold text-[#a0522d] uppercase tracking-wider">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <div class="shrink-0 p-1.5 bg-orange-500 rounded-full">
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover rounded-full border-4 border-[#fdf6ec]">
                </div>
            @endif
        </header>

        <div class="px-12 mt-4 flex flex-wrap gap-2">
            @if ($user->phone)
                <span class="text-xs font-bold text-white bg-orange-500 rounded-full px-3.5 py-1.5">{{ $user->phone }}</span>
            @endif
            <span class="text-xs font-bold text-white bg-amber-500 rounded-full px-3.5 py-1.5 break-all">{{ $user->email }}</span>
            @if ($user->address || $user->city)
                <span class="text-xs font-bold text-white bg-[#a0522d] rounded-full px-3.5 py-1.5">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)
                <span class="text-xs font-bold text-white bg-orange-400 rounded-full px-3.5 py-1.5">{{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </div>

        <div class="px-12 py-9">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-lg font-black uppercase text-[#a0522d] border-b-4 border-orange-500 pb-2 inline-block">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-lg font-black uppercase text-[#a0522d] border-b-4 border-orange-500 pb-2 inline-block">Erfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="flex gap-5 items-start">
                                <div class="shrink-0 w-14 h-14 rounded-full {{ $loop->odd ? 'bg-orange-500' : 'bg-amber-400' }} text-white flex items-center justify-center font-black text-lg tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('y') }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                        <h3 class="text-lg font-extrabold leading-snug">{{ $job['title'] }}</h3>
                                        <p class="text-xs font-bold text-[#a0522d] whitespace-nowrap tabular-nums">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                            –
                                            {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                    </div>
                                    <p class="text-sm font-semibold text-orange-600">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-[#5c4636]">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-lg font-black uppercase text-[#a0522d] border-b-4 border-orange-500 pb-2 inline-block">Kompetencer</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-bold text-[#a0522d] border-2 border-orange-400 px-3.5 py-1.5 rounded-full">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <div class="h-2.5 bg-[#a0522d]"></div>
        <div class="h-2.5 bg-amber-400"></div>
        <div class="h-2.5 bg-orange-500"></div>
    </div>
</body>
</html>
