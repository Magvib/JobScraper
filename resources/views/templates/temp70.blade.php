{{-- Sage Grid --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#f6f7f2] shadow-md min-h-[297mm] font-sans text-[#37402f] px-12 py-11">

        {{-- Rolig salvie header --}}
        <header class="flex items-center gap-6 pb-7 border-b-2 border-[#a8b39a]">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-20 h-20 object-cover rounded-lg shrink-0 border-2 border-[#a8b39a]">
            @endif
            <div class="flex-1">
                <h1 class="text-3xl font-bold tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1 text-[#6b7a5c] font-medium">{{ $user->job_title }}</p>
                @endif
            </div>
            <div class="text-right text-xs text-[#6b7a5c] space-y-1">
                @if ($user->phone)
                    <p>{{ $user->phone }}</p>
                @endif
                <p class="break-all">{{ $user->email }}</p>
                @if ($user->address || $user->city)
                    <p>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                @endif
            </div>
        </header>

        <div class="mt-8 grid gap-8">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-xs font-bold uppercase tracking-[0.25em] text-[#6b7a5c]">◆ Ansøgning</h2>
                    <div class="mt-4 bg-white rounded-lg border border-[#dfe3d5] p-7 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-xs font-bold uppercase tracking-[0.25em] text-[#6b7a5c]">◆ Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-4 grid gap-3">
                        @foreach ($jobs as $job)
                            <article class="bg-white rounded-lg border border-[#dfe3d5] grid grid-cols-[110px_1fr] overflow-hidden">
                                <div class="bg-[#e8ecdf] flex flex-col items-center justify-center py-4 border-r border-[#dfe3d5]">
                                    <p class="text-sm font-black text-[#4d5a3e] tabular-nums">{{ \Carbon\Carbon::parse($job['startDate'])->format('Y') }}</p>
                                    <p class="text-[10px] font-bold text-[#8a967a] uppercase tracking-wider">
                                        – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('Y') : 'nu' }}
                                    </p>
                                </div>
                                <div class="px-5 py-4">
                                    <h3 class="font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm text-[#6b7a5c] font-medium">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-1.5 leading-relaxed text-[#4a543d]">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="grid grid-cols-2 gap-8">
                @if ($skills)
                    <section>
                        <h2 class="text-xs font-bold uppercase tracking-[0.25em] text-[#6b7a5c]">◆ Kompetencer</h2>
                        <div class="mt-4 bg-white rounded-lg border border-[#dfe3d5] p-5 flex flex-wrap gap-2">
                            @foreach ($skills as $skill)
                                <span class="text-xs font-semibold text-[#4d5a3e] bg-[#e8ecdf] px-2.5 py-1 rounded">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section>
                    <h2 class="text-xs font-bold uppercase tracking-[0.25em] text-[#6b7a5c]">◆ Personligt</h2>
                    <div class="mt-4 bg-white rounded-lg border border-[#dfe3d5] p-5 text-sm space-y-1.5 text-[#4a543d]">
                        @if ($user->birthdate)
                            <p><span class="font-semibold">Født:</span> {{ $user->birthdate->format('d/m/Y') }}</p>
                        @endif
                        <p><span class="font-semibold">Adresse:</span> {{ collect([$user->address, $user->zip, $user->city])->filter()->implode(', ') }}</p>
                        @if ($user->phone)
                            <p><span class="font-semibold">Telefon:</span> {{ $user->phone }}</p>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </div>
</body>
</html>
