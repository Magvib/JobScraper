{{-- Basalt Cards --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#eef0f2] shadow-md min-h-[297mm] font-sans text-stone-800 px-10 py-10">

        {{-- Hoved-kort --}}
        <header class="bg-white rounded-2xl shadow-sm border border-stone-200 px-9 py-8 flex items-center gap-7">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-20 h-20 object-cover rounded-2xl shrink-0 border border-stone-200">
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-[#2f436b]">Curriculum Vitae</p>
                <h1 class="mt-2 font-serif text-4xl font-bold tracking-tight text-stone-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-lg text-stone-500">{{ $user->job_title }}</p>
                @endif
            </div>
            <div class="text-right text-xs text-stone-600 space-y-1 shrink-0">
                @if ($user->phone)
                    <p>{{ $user->phone }}</p>
                @endif
                <p class="break-all">{{ $user->email }}</p>
                @if ($user->address || $user->city)
                    <p>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                @endif
                @if ($user->birthdate)
                    <p>Født {{ $user->birthdate->format('d/m/Y') }}</p>
                @endif
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-5 bg-white rounded-2xl shadow-sm border border-stone-200 px-9 py-7">
                <h2 class="text-[10px] font-extrabold uppercase tracking-[0.3em] text-[#2f436b]">Ansøgning</h2>
                <div class="mt-4 space-y-4 text-[15px] leading-relaxed">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-5 space-y-4">
                @foreach ($jobs as $job)
                    <article class="bg-white rounded-2xl shadow-sm border border-stone-200 px-8 py-5 flex gap-6 items-start">
                        <div class="shrink-0 w-[26mm] text-center bg-[#2f436b] text-white rounded-lg px-2 py-2.5">
                            <p class="text-sm font-bold tabular-nums leading-tight">{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}</p>
                            <p class="text-[10px] text-white/80 leading-tight">– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}</p>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-serif text-xl font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                            <p class="text-sm text-[#2f436b]">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-1.5 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>
        @endif

        @if ($skills)
            <section class="mt-5 bg-white rounded-2xl shadow-sm border border-stone-200 px-8 py-6">
                <h2 class="text-[10px] font-extrabold uppercase tracking-[0.3em] text-[#2f436b]">Kompetencer</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-xs font-semibold text-stone-700 bg-stone-100 border border-stone-200 px-3 py-1.5 rounded-full">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-5 bg-white rounded-2xl shadow-sm border border-stone-200 px-8 py-6">
                <h2 class="text-[10px] font-extrabold uppercase tracking-[0.3em] text-[#2f436b]">Links</h2>
                <ul class="mt-4 flex flex-wrap gap-x-12 gap-y-3 text-sm">
                    @foreach ($links as $link)
                        <li>
                            <p class="font-bold text-stone-900">{{ $link->name }}</p>
                            <a href="{{ $link->url }}" class="text-[#2f436b] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-5 flex justify-between text-[10px] uppercase tracking-[0.3em] text-stone-400 px-1">
            <span>{{ $user->name }}</span>
            <span>Curriculum Vitae</span>
        </footer>
    </div>
</body>
</html>