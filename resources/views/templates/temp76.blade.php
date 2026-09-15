{{-- Linen Natural --}}
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
    <div class="cv-page max-w-[210mm] mx-auto shadow-md min-h-[297mm] font-sans text-[#4c4437] bg-[#f9f6ef]" style="background-image: repeating-linear-gradient(0deg, rgba(120,110,90,0.04) 0 1px, transparent 1px 4px);">

        <div class="px-14 py-12">
            <header class="flex items-center gap-8 pb-7 border-b border-[#d8d0bc]">
                <div class="flex-1">
                    <h1 class="text-4xl font-medium tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-lg text-[#8a7f68]">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-28 object-cover shrink-0 rounded shadow-sm border border-[#d8d0bc]">
                @endif
            </header>

            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-1 text-sm text-[#8a7f68]">
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

            @if (isset($coverLetter))
                <section class="mt-9">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.3em] text-[#8a7f68]">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-9">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.3em] text-[#8a7f68]">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-0">
                        @foreach ($jobs as $job)
                            <article class="py-4 border-b border-[#e4ddca] last:border-0 grid grid-cols-[105px_1fr] gap-5">
                                <p class="text-xs font-semibold text-[#a39a80] tabular-nums pt-1">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>
                                    – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <div>
                                    <h3 class="font-semibold text-lg leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm text-[#8a7f68]">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-1.5 leading-relaxed text-[#5d5342]">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-8">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.3em] text-[#8a7f68]">Kompetencer</h2>
                    <ul class="mt-4 grid grid-cols-2 gap-x-8 gap-y-1.5 text-sm">
                        @foreach ($skills as $skill)
                            <li class="flex items-center gap-2.5">
                                <span class="w-1 h-1 rounded-full bg-[#a39a80]"></span>{{ $skill }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </div>
</body>
</html>
