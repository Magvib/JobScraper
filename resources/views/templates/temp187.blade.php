{{-- Centerline --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-stone-800 shadow-lg min-h-[297mm] font-sans px-14 py-12 flex flex-col">

        {{-- Centreret dokumenthoved --}}
        <header class="text-center">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 rounded-full object-cover mx-auto ring-1 ring-stone-300 ring-offset-4">
            @endif
            <p class="mt-6 text-[10px] font-bold uppercase tracking-[0.5em] text-[#4c5f9e]">Curriculum Vitae</p>
            <h1 class="mt-3 font-serif text-5xl font-bold leading-tight text-stone-900">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 text-lg italic text-stone-500">{{ $user->job_title }}</p>
            @endif
            <div class="mt-5 flex flex-wrap justify-center gap-x-4 gap-y-1 text-xs text-stone-600">
                @if ($user->phone)
                    <span>{{ $user->phone }}</span>
                    <span class="text-[#4c5f9e]">·</span>
                @endif
                <span class="break-all">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span class="text-[#4c5f9e]">·</span>
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span class="text-[#4c5f9e]">·</span>
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>
        </header>

        <div class="mt-8 border-t border-stone-200"></div>

        {{-- Indhold i smal centreret kolonne --}}
        <div class="flex-1 w-[158mm] mx-auto">
            @if (isset($coverLetter))
                <section class="mt-9">
                    <h2 class="flex items-center justify-center gap-4 text-[10px] font-bold uppercase tracking-[0.4em] text-[#4c5f9e]">
                        <span class="h-px w-12 bg-stone-300"></span>
                        Ansøgning
                        <span class="h-px w-12 bg-stone-300"></span>
                    </h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-stone-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section class="mt-9">
                    <h2 class="flex items-center justify-center gap-4 text-[10px] font-bold uppercase tracking-[0.4em] text-[#4c5f9e]">
                        <span class="h-px w-12 bg-stone-300"></span>
                        Erhvervserfaring &amp; Uddannelse
                        <span class="h-px w-12 bg-stone-300"></span>
                    </h2>
                    <div class="mt-6 divide-y divide-stone-200">
                        @foreach ($jobs as $job)
                            <article class="py-6 first:pt-0">
                                <p class="text-center text-[10px] font-bold uppercase tracking-[0.25em] text-stone-400 tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1.5 text-center font-serif text-2xl font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                <p class="mt-0.5 text-center text-sm italic text-[#4c5f9e]">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-3 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="flex items-center justify-center gap-4 text-[10px] font-bold uppercase tracking-[0.4em] text-[#4c5f9e]">
                        <span class="h-px w-12 bg-stone-300"></span>
                        Kompetencer
                        <span class="h-px w-12 bg-stone-300"></span>
                    </h2>
                    <p class="mt-6 text-center text-sm leading-8 text-stone-700">
                        @foreach ($skills as $i => $skill)
                            {{ $skill }}@if ($i < count($skills) - 1)<span class="text-[#4c5f9e]"> · </span>@endif
                        @endforeach
                    </p>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-9">
                    <h2 class="flex items-center justify-center gap-4 text-[10px] font-bold uppercase tracking-[0.4em] text-[#4c5f9e]">
                        <span class="h-px w-12 bg-stone-300"></span>
                        Links
                        <span class="h-px w-12 bg-stone-300"></span>
                    </h2>
                    <ul class="mt-6 flex flex-wrap justify-center gap-x-8 gap-y-2 text-sm">
                        @foreach ($links as $link)
                            <li class="text-center">
                                <p class="font-bold text-stone-900">{{ $link->name }}</p>
                                <a href="{{ $link->url }}" class="text-[#4c5f9e] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        {{-- Centreret footer --}}
        <footer class="mt-10 text-center">
            <div class="border-t border-stone-200"></div>
            <p class="mt-3 text-[10px] uppercase tracking-[0.3em] text-stone-400">{{ $user->name }} — Curriculum Vitae</p>
        </footer>
    </div>
</body>
</html>