{{-- Pinstripe Banker --}}
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
        .pinstripe {
            background: repeating-linear-gradient(
                90deg,
                #0f1e3d,
                #0f1e3d 5px,
                #16294f 5px,
                #16294f 10px
            );
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-serif text-slate-900 overflow-hidden">

        {{-- Navystribet hovedbånd --}}
        <header class="pinstripe text-white px-14 py-12 flex items-center gap-9 border-b-4 border-slate-300">
            @if ($photo)
                <div class="w-24 h-24 shrink-0 border-2 border-white p-1">
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover grayscale">
                </div>
            @endif
            <div>
                <h1 class="text-4xl font-bold tracking-wide">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-2 text-base italic text-slate-300">{{ $user->job_title }}</p>
                @endif
            </div>
            <div class="ml-auto text-right text-xs leading-relaxed text-slate-200 font-sans">
                @if ($user->phone)
                    <p>{{ $user->phone }}</p>
                @endif
                <p class="break-all">{{ $user->email }}</p>
                @if ($user->address || $user->city)
                    <p>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                @endif
            </div>
        </header>

        <div class="px-14 py-11">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-sm font-bold uppercase tracking-[0.3em] text-[#0f1e3d] border-b-2 border-[#0f1e3d] pb-2">Ansøgning</h2>
                    <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-slate-700">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-sm font-bold uppercase tracking-[0.3em] text-[#0f1e3d] border-b-2 border-[#0f1e3d] pb-2">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 space-y-6">
                        @foreach ($jobs as $job)
                            <article class="grid grid-cols-[120px_1fr] gap-6">
                                <div class="text-xs font-sans font-semibold uppercase tracking-wider text-slate-400 tabular-nums pt-1 text-right">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    <br>– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </div>
                                <div class="border-l-2 border-slate-300 pl-5">
                                    <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                    <p class="text-sm italic text-slate-500">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-slate-600 text-justify">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-9">
                    <h2 class="text-sm font-bold uppercase tracking-[0.3em] text-[#0f1e3d] border-b-2 border-[#0f1e3d] pb-2">Kompetencer</h2>
                    <div class="mt-5 grid grid-cols-3 gap-x-8">
                        @foreach ($skills as $skill)
                            <p class="text-sm font-medium border-b border-dotted border-slate-300 py-1.5">{{ $skill }}</p>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-9">
                    <h2 class="text-sm font-bold uppercase tracking-[0.3em] text-[#0f1e3d] border-b-2 border-[#0f1e3d] pb-2">Links</h2>
                    <ul class="mt-5 text-sm space-y-1">
                        @foreach ($links as $link)
                            <li>
                                <span class="font-semibold">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-slate-600 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <footer class="mt-11 pt-4 border-t-4 border-double border-slate-400 flex justify-between font-sans text-xs text-slate-500">
                <span>{{ $user->name }}</span>
                @if ($user->birthdate)
                    <span>Født {{ $user->birthdate->format('d/m/Y') }}</span>
                @endif
            </footer>
        </div>
    </div>
</body>
</html>