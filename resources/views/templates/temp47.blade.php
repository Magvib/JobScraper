{{-- Graphite Split --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-zinc-900">

        {{-- 50/50 split header --}}
        <header class="grid grid-cols-2">
            <div class="bg-zinc-900 text-white px-10 py-12">
                <h1 class="text-4xl font-black tracking-tight leading-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-zinc-300 text-lg">{{ $user->job_title }}</p>
                @endif
            </div>
            <div class="bg-zinc-100 px-10 py-12 flex flex-col justify-center gap-1.5 text-sm text-zinc-700">
                @if ($user->phone)
                    <p><span class="font-bold text-zinc-900">T:</span> {{ $user->phone }}</p>
                @endif
                <p class="break-all"><span class="font-bold text-zinc-900">M:</span> {{ $user->email }}</p>
                @if ($user->address || $user->city)
                    <p><span class="font-bold text-zinc-900">A:</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                @endif
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-16 h-16 object-cover rounded-full mt-3 border-2 border-zinc-900">
                @endif
            </div>
        </header>

        <div class="px-12 py-10">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-lg font-black uppercase tracking-tight">
                        <span class="bg-zinc-900 text-white px-2 py-0.5">Ansøgning</span>
                    </h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-relaxed">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-lg font-black uppercase tracking-tight">
                        <span class="bg-zinc-900 text-white px-2 py-0.5">Erhvervserfaring</span>
                        &amp; uddannelse
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article>
                                <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                    <h3 class="text-lg font-extrabold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-xs font-bold text-zinc-500 whitespace-nowrap tabular-nums">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                        →
                                        {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-zinc-500 uppercase tracking-wide">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-zinc-700">{{ $job['description'] }}</p>
                                @endif
                                <div class="mt-3 h-px bg-zinc-200"></div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="text-lg font-black uppercase tracking-tight">
                        <span class="bg-zinc-900 text-white px-2 py-0.5">Kompetencer</span>
                    </h2>
                    <div class="mt-5 grid grid-cols-3 gap-2">
                        @foreach ($skills as $skill)
                            <p class="text-sm font-semibold border border-zinc-300 px-3 py-2 text-center">{{ $skill }}</p>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-10">
                    <h2 class="text-lg font-black uppercase tracking-tight">
                        <span class="bg-zinc-900 text-white px-2 py-0.5">Links</span>
                    </h2>
                    <ul class="mt-5 grid grid-cols-2 gap-2">
                        @foreach ($links as $link)
                            <li class="text-sm border border-zinc-300 px-3 py-2">
                                <span class="font-semibold">{{ $link->name }}</span>
                                <a href="{{ $link->url }}" class="block text-zinc-600 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($user->birthdate)
                <p class="mt-10 text-xs text-zinc-500 uppercase tracking-widest">Fødselsdato: {{ $user->birthdate->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>
</body>
</html>
