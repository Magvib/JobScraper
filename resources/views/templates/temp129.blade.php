{{-- Postcard --}}
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
        /* Perforeret frimærke-kant */
        .stamp {
            background-image: radial-gradient(circle at 0 0, transparent 4px, #0d9488 4px),
                             radial-gradient(circle at 0 100%, transparent 4px, #0d9488 4px),
                             radial-gradient(circle at 100% 0, transparent 4px, #0d9488 4px),
                             radial-gradient(circle at 100% 100%, transparent 4px, #0d9488 4px);
            background-size: 10px 10px;
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fbf7ee] shadow-xl min-h-[297mm] font-sans text-stone-800 p-8">
        <div class="bg-white border border-stone-300 min-h-[281mm] px-10 py-10 relative shadow-sm">

            {{-- Brevkortets bagside-æstetik: venstre besked, højre adresse --}}
            <header class="flex items-start justify-between gap-8 pb-7 border-b border-stone-300">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.4em] text-teal-700">Curriculum Vitae · Postkort</p>
                    <h1 class="mt-2.5 text-4xl font-extrabold tracking-tight text-stone-900">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-base font-semibold text-teal-700">{{ $user->job_title }}</p>
                    @endif
                </div>
                <div class="shrink-0 text-center">
                    {{-- Frimærke med foto --}}
                    <div class="stamp w-24 h-24 p-1.5 rotate-2">
                        @if ($photo)
                            <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-teal-50 flex items-center justify-center text-teal-700 font-serif text-xs italic">CV</div>
                        @endif
                    </div>
                    <div class="mt-1.5 w-24 h-24 -mt-14 -ml-2 border-2 border-dashed border-teal-500/60 rounded-full pointer-events-none"></div>
                </div>
            </header>

            <div class="mt-8 flex gap-10">
                {{-- Venstre side: indhold --}}
                <div class="flex-1">
                    @if (isset($coverLetter))
                        <section>
                            <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-teal-700">Ansøgning</h2>
                            <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-stone-700">
                                {!! $coverLetter->renderContext() !!}
                            </div>
                        </section>
                    @elseif ($jobs)
                        <section>
                            <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-teal-700">Erhvervserfaring &amp; uddannelse</h2>
                            <div class="mt-6 space-y-6">
                                @foreach ($jobs as $job)
                                    <article>
                                        <p class="text-[11px] font-bold tracking-widest text-stone-400 tabular-nums uppercase">
                                            {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                        </p>
                                        <h3 class="mt-1 font-bold text-lg leading-snug text-stone-900">{{ $job['title'] }}</h3>
                                        <p class="text-sm font-medium text-teal-700">{{ $job['company'] }}</p>
                                        @if (!empty($job['description']))
                                            <p class="text-sm mt-2 leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($skills)
                        <section class="mt-9">
                            <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-teal-700">Kompetencer</h2>
                            <p class="mt-4 text-sm leading-loose font-medium text-stone-700">
                                {{ implode('   ·   ', $skills) }}
                            </p>
                        </section>
                    @endif
                </div>

                {{-- Højre side: adressedel med linjer --}}
                <div class="w-[62mm] shrink-0 border-l border-stone-300 pl-7">
                    <div class="mt-2 text-sm text-stone-600 leading-loose">
                        <p class="font-bold text-stone-900">{{ $user->name }}</p>
                        @if ($user->address)
                            <p>{{ $user->address }}</p>
                        @endif
                        @if ($user->city)
                            <p>{{ trim(($user->zip ?? '') . ' ' . ($user->city ?? '')) }}</p>
                        @endif
                        <div class="my-5 border-t border-stone-300"></div>
                        @if ($user->phone)
                            <p>Tlf: {{ $user->phone }}</p>
                        @endif
                        <p class="break-all">{{ $user->email }}</p>
                        @if ($user->birthdate)
                            <p>Født {{ $user->birthdate->format('d/m/Y') }}</p>
                        @endif
                    </div>
                    <div class="mt-8 space-y-6 pt-6 border-t border-stone-300">
                        <div class="h-8 border-b border-stone-400"></div>
                        <div class="h-8 border-b border-stone-400"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>