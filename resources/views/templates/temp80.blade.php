{{-- Wine & Cream --}}
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
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#faf5ee] shadow-lg min-h-[297mm] font-serif text-[#40111d] px-14 py-12">

        <header class="pb-7 border-b-2 border-[#6b1e2e]">
            <div class="flex items-start justify-between gap-8">
                <div class="flex-1">
                    <h1 class="text-4xl font-bold tracking-wide">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-lg italic text-[#8a3547]">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-28 h-36 object-cover shrink-0 border-4 border-[#6b1e2e]/20 shadow-md">
                @endif
            </div>
            <div class="mt-5 flex flex-wrap gap-x-6 gap-y-1 text-sm text-[#7c4a56]">
                @if ($user->phone)
                    <span>☎ {{ $user->phone }}</span>
                @endif
                <span class="break-all">✉ {{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span>⌂ {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="text-lg font-bold text-[#6b1e2e] flex items-center gap-4">
                    Ansøgning <span class="flex-1 border-t border-[#c9a0aa] border-dotted"></span>
                </h2>
                <div class="mt-5 space-y-4 leading-relaxed text-[15px]">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="text-lg font-bold text-[#6b1e2e] flex items-center gap-4">
                    Erhvervserfaring &amp; uddannelse <span class="flex-1 border-t border-[#c9a0aa] border-dotted"></span>
                </h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="flex gap-6">
                            <div class="shrink-0 w-20 pt-0.5 text-center">
                                <p class="text-sm font-bold text-[#6b1e2e] tabular-nums">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}</p>
                                <p class="text-[10px] uppercase text-[#a36874]">til</p>
                                <p class="text-sm font-bold text-[#6b1e2e] tabular-nums">{{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}</p>
                            </div>
                            <div class="flex-1 pb-5 border-b border-[#e3cdd2]">
                                <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm italic text-[#8a3547]">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-[#54303a]">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-8">
                <h2 class="text-lg font-bold text-[#6b1e2e] flex items-center gap-4">
                    Kompetencer <span class="flex-1 border-t border-[#c9a0aa] border-dotted"></span>
                </h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-sm text-[#6b1e2e] bg-[#f0e0e3] px-3.5 py-1.5 border border-[#d9b6bd]">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-8">
                <h2 class="text-lg font-bold text-[#6b1e2e] flex items-center gap-4">
                    Links <span class="flex-1 border-t border-[#c9a0aa] border-dotted"></span>
                </h2>
                <ul class="mt-4 space-y-1.5 text-sm">
                    @foreach ($links as $link)
                        <li>
                            <span class="font-semibold">{{ $link->name }}:</span>
                            <a href="{{ $link->url }}" class="text-[#8a3547] underline break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-10 pt-4 border-t-2 border-[#6b1e2e] text-center text-xs tracking-[0.3em] uppercase text-[#a36874]">
            {{ collect([$user->name, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
        </footer>
    </div>
</body>
</html>
