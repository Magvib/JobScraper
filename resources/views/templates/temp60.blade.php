{{-- Fjord Blue --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white text-slate-800 shadow-lg min-h-[297mm] font-sans flex flex-col">

        {{-- Rolig hovedbjælke i fjordblåt --}}
        <header class="bg-[#1e4e6d] text-white px-14 pt-12 pb-10">
            <div class="flex items-center justify-between gap-10">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-white/60">Curriculum Vitae</p>
                    <h1 class="mt-3 text-4xl font-bold tracking-tight leading-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-base font-light text-white/85">{{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 rounded-xl object-cover ring-2 ring-white/40 shrink-0">
                @endif
            </div>
            <div class="mt-6 pt-4 border-t border-white/20 flex flex-wrap gap-x-6 gap-y-1 text-xs text-white/80">
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
        </header>

        <div class="px-14 py-10 grid grid-cols-[1fr_50mm] gap-12 flex-1">
            {{-- Erhvervserfaring --}}
            <main>
                @if (isset($coverLetter))
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-[#1e4e6d] border-b border-slate-200 pb-2">Ansøgning</h2>
                    <div class="mt-6 space-y-4">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                @elseif ($jobs)
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-[#1e4e6d] border-b border-slate-200 pb-2">Erhvervserfaring &amp; Uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="relative pl-5 border-l-2 border-slate-200">
                                <span class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-[#1e4e6d]"></span>
                                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <h3 class="mt-1 text-lg font-bold leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                <p class="text-sm text-[#1e4e6d]">{{ $job['company'] }}</p>
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
                        <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-[#1e4e6d] border-b border-slate-200 pb-2">Kompetencer</h2>
                        <ul class="mt-5 space-y-2.5 text-sm text-slate-700">
                            @foreach ($skills as $skill)
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-[7px] w-1.5 h-1.5 bg-[#1e4e6d] shrink-0"></span>
                                    <span>{{ $skill }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($links->isNotEmpty())
                        <h2 class="mt-8 text-xs font-bold uppercase tracking-[0.3em] text-[#1e4e6d] border-b border-slate-200 pb-2">Links</h2>
                        <ul class="mt-5 space-y-3 text-sm">
                            @foreach ($links as $link)
                                <li>
                                    <p class="font-bold text-slate-800">{{ $link->name }}</p>
                                    <a href="{{ $link->url }}" class="text-[#1e4e6d] underline underline-offset-2 break-all">{{ $link->prettifyUrl() }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </aside>
            @endif
        </div>

        {{-- Diskret footer --}}
        <footer class="border-t border-slate-200 px-14 py-3 flex justify-between text-[10px] uppercase tracking-[0.3em] text-slate-400">
            <span>{{ $user->name }}</span>
            <span>Curriculum Vitae</span>
        </footer>
    </div>
</body>
</html>