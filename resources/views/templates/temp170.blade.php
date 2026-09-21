{{-- Quiet Frame --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-slate-800 shadow-lg min-h-[297mm] font-sans p-5">
        {{-- Tynd dobbeltramme omkring hele siden --}}
        <div class="border border-slate-300 h-full min-h-[277mm] p-[7px] flex flex-col">
            <div class="border border-slate-300 px-12 py-10 flex flex-col flex-1">

                <header class="flex items-center justify-between gap-10 pb-7">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-[#4a5a6e]">Curriculum Vitae</p>
                        <h1 class="mt-3 font-serif text-5xl font-bold leading-tight text-slate-900">{{ $user->name }}</h1>
                        @if ($user->job_title)
                            <p class="mt-2 font-serif text-lg italic text-slate-500">{{ $user->job_title }}</p>
                        @endif
                    </div>
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}"
                             class="w-28 h-28 rounded-full object-cover ring-1 ring-slate-300 ring-offset-4 shrink-0">
                    @endif
                </header>

                {{-- Kontakt-række --}}
                <div class="border-y border-slate-200 py-3 flex flex-wrap gap-x-6 gap-y-1 text-xs text-slate-600">
                    @if ($user->phone)
                        <span><span class="font-bold text-slate-900">Tlf</span> {{ $user->phone }}</span>
                    @endif
                    <span class="break-all"><span class="font-bold text-slate-900">Mail</span> {{ $user->email }}</span>
                    @if ($user->address || $user->city)
                        <span><span class="font-bold text-slate-900">Adresse</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
                    @endif
                    @if ($user->birthdate)
                        <span><span class="font-bold text-slate-900">Født</span> {{ $user->birthdate->format('d/m/Y') }}</span>
                    @endif
                </div>

                <div class="mt-8 grid grid-cols-[1fr_50mm] gap-12 flex-1">
                    {{-- Erhvervserfaring --}}
                    <main>
                        @if (isset($coverLetter))
                            <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#4a5a6e] border-b border-slate-200 pb-2.5">Ansøgning</h2>
                            <div class="mt-6 space-y-4">
                                {!! $coverLetter->renderContext() !!}
                            </div>
                        @elseif ($jobs)
                            <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#4a5a6e] border-b border-slate-200 pb-2.5">Erhvervserfaring &amp; Uddannelse</h2>
                            <div class="mt-6 space-y-6">
                                @foreach ($jobs as $job)
                                    <article>
                                        <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-400 tabular-nums">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                            –
                                            {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                        <h3 class="mt-1 font-serif text-xl font-bold leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                        <p class="text-sm italic text-[#4a5a6e]">{{ $job['company'] }}</p>
                                        @if (!empty($job['description']))
                                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $job['description'] }}</p>
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
                                <h2 class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#4a5a6e] border-b border-slate-200 pb-2.5">Kompetencer</h2>
                                <ul class="mt-5 space-y-2.5 text-sm text-slate-700">
                                    @foreach ($skills as $skill)
                                        <li class="flex items-start gap-2.5">
                                            <span class="mt-[7px] w-1.5 h-1.5 bg-[#4a5a6e] shrink-0"></span>
                                            <span>{{ $skill }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($links->isNotEmpty())
                                <h2 class="mt-8 text-[10px] font-bold uppercase tracking-[0.35em] text-[#4a5a6e] border-b border-slate-200 pb-2.5">Links</h2>
                                <ul class="mt-5 space-y-3 text-sm">
                                    @foreach ($links as $link)
                                        <li>
                                            <p class="font-bold text-slate-900">{{ $link->name }}</p>
                                            <a href="{{ $link->url }}" class="text-[#4a5a6e] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </aside>
                    @endif
                </div>

                {{-- Footer inden for rammen --}}
                <footer class="mt-10 border-t border-slate-200 pt-3 flex justify-between text-[10px] uppercase tracking-[0.3em] text-slate-400">
                    <span>{{ $user->name }}</span>
                    <span>Curriculum Vitae</span>
                </footer>
            </div>
        </div>
    </div>
</body>
</html>