{{-- Khaki Field --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#ede9dc] shadow-lg min-h-[297mm] font-sans text-[#39392b] border-4 border-[#6b6b4e]">

        {{-- Felt-blazer look --}}
        <header class="px-12 pt-10 pb-7 border-b-2 border-dashed border-[#9a9a78]">
            <div class="flex items-center gap-7">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-28 object-cover shrink-0 border-2 border-[#6b6b4e] p-1 bg-white">
                @endif
                <div class="flex-1">
                    <h1 class="text-4xl font-black uppercase tracking-wide">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 bg-[#6b6b4e] text-[#f2efe3] text-sm font-bold uppercase tracking-widest inline-block px-3 py-1">{{ $user->job_title }}</p>
                    @endif
                </div>
            </div>
            <div class="mt-5 flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-wider text-[#6b6b4e]">
                @if ($user->phone)
                    <span class="border border-[#9a9a78] px-2.5 py-1">{{ $user->phone }}</span>
                @endif
                <span class="border border-[#9a9a78] px-2.5 py-1 break-all normal-case">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span class="border border-[#9a9a78] px-2.5 py-1">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span class="border border-[#9a9a78] px-2.5 py-1">{{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>
        </header>

        <div class="px-12 py-8">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-sm font-black uppercase tracking-[0.25em] text-[#6b6b4e] flex items-center gap-3">
                        <span class="w-2.5 h-2.5 border-2 border-[#6b6b4e]"></span> Ansøgning
                    </h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-sm font-black uppercase tracking-[0.25em] text-[#6b6b4e] flex items-center gap-3">
                        <span class="w-2.5 h-2.5 border-2 border-[#6b6b4e]"></span> Erhvervserfaring &amp; uddannelse
                    </h2>
                    <div class="mt-6 space-y-5">
                        @foreach ($jobs as $job)
                            <article class="border-2 border-[#9a9a78] bg-[#f2efe3] px-6 py-4">
                                <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                    <h3 class="text-lg font-extrabold uppercase tracking-wide leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-black text-[#6b6b4e] whitespace-nowrap tabular-nums bg-[#e3dfcc] px-2.5 py-1">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        –
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-bold text-[#7a7a58] uppercase tracking-wider mt-0.5">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-[#4d4d39]">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-sm font-black uppercase tracking-[0.25em] text-[#6b6b4e] flex items-center gap-3">
                        <span class="w-2.5 h-2.5 border-2 border-[#6b6b4e]"></span> Kompetencer
                    </h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-xs font-extrabold uppercase tracking-wider text-[#f2efe3] bg-[#6b6b4e] px-3 py-1.5">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</body>
</html>
