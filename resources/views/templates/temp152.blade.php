{{-- Symmetric Serif --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-stone-800 shadow-lg min-h-[297mm] font-sans px-14 pt-0 pb-12 flex flex-col">

        {{-- Øxblood-topbjælke --}}
        <div class="h-1.5 bg-[#7a2230]"></div>

        {{-- Centreret klassisk hoved --}}
        <header class="pt-10 text-center">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 rounded-full object-cover mx-auto ring-1 ring-stone-300 ring-offset-4">
            @endif
            <p class="mt-6 text-[10px] font-bold uppercase tracking-[0.45em] text-[#7a2230]">Curriculum Vitae</p>
            <h1 class="mt-3 font-serif text-5xl font-bold leading-tight text-stone-900">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-2 font-serif text-lg italic text-stone-500">{{ $user->job_title }}</p>
            @endif
            <div class="mt-6 mx-auto max-w-[150mm] border-y border-stone-200 py-2.5 text-xs text-stone-600 flex flex-wrap justify-center gap-x-4 gap-y-1">
                @if ($user->phone)
                    <span>{{ $user->phone }}</span>
                    <span class="text-[#7a2230]">·</span>
                @endif
                <span class="break-all">{{ $user->email }}</span>
                @if ($user->address || $user->city)
                    <span class="text-[#7a2230]">·</span>
                    <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                @endif
                @if ($user->birthdate)
                    <span class="text-[#7a2230]">·</span>
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </div>
        </header>

        <div class="mt-9 grid grid-cols-[1fr_50mm] gap-12 flex-1 items-start">
            {{-- Erhvervserfaring --}}
            <main>
                @if (isset($coverLetter))
                    <h2 class="text-center text-[10px] font-bold uppercase tracking-[0.35em] text-[#7a2230]">
                        Ansøgning
                        <span class="mx-auto mt-2 block h-px w-10 bg-[#7a2230]"></span>
                    </h2>
                    <div class="mt-6 space-y-4">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                @elseif ($jobs)
                    <h2 class="text-center text-[10px] font-bold uppercase tracking-[0.35em] text-[#7a2230]">
                        Erhvervserfaring &amp; Uddannelse
                        <span class="mx-auto mt-2 block h-px w-10 bg-[#7a2230]"></span>
                    </h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article>
                                <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-stone-400 tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 font-serif text-xl font-bold leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                <p class="text-sm italic text-[#7a2230]">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </main>

            {{-- Kompetencer & links --}}
            @if ($skills || $links->isNotEmpty())
                <aside>
                    @if ($skills)
                        <h2 class="text-center text-[10px] font-bold uppercase tracking-[0.35em] text-[#7a2230]">
                            Kompetencer
                            <span class="mx-auto mt-2 block h-px w-10 bg-[#7a2230]"></span>
                        </h2>
                        <p class="mt-5 text-center text-sm leading-7 text-stone-700">
                            @foreach ($skills as $i => $skill)
                                {{ $skill }}@if ($i < count($skills) - 1)<span class="text-[#7a2230]"> · </span>@endif
                            @endforeach
                        </p>
                    @endif

                    @if ($links->isNotEmpty())
                        <h2 class="mt-8 text-center text-[10px] font-bold uppercase tracking-[0.35em] text-[#7a2230]">
                            Links
                            <span class="mx-auto mt-2 block h-px w-10 bg-[#7a2230]"></span>
                        </h2>
                        <ul class="mt-5 space-y-2.5 text-center text-sm">
                            @foreach ($links as $link)
                                <li>
                                    <p class="font-bold text-stone-800">{{ $link->name }}</p>
                                    <a href="{{ $link->url }}" class="text-[#7a2230] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </aside>
            @endif
        </div>

        {{-- Centreret footer --}}
        <footer class="mt-10 text-center">
            <span class="mx-auto block h-px w-10 bg-stone-300"></span>
            <p class="mt-3 text-[10px] uppercase tracking-[0.3em] text-stone-400">{{ $user->name }} — Curriculum Vitae</p>
        </footer>
    </div>
</body>
</html>