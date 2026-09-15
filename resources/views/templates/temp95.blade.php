{{-- Brass Formal --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#26221a] shadow-2xl min-h-[297mm] font-serif text-[#e8e0cd] px-14 py-12">

        {{-- Mørk messing formal --}}
        <header class="text-center pb-7 border-b border-[#a8894f]">
            <div class="border border-[#a8894f] -m-2 p-2 inline-block w-full">
                <div class="py-6">
                    @if ($photo)
                        <img src="{{ $photo }}" alt="{{ $user->name }}"
                             class="w-24 h-24 object-cover rounded-full mx-auto mb-4 sepia border-2 border-[#c9a96a]">
                    @endif
                    <h1 class="text-4xl font-bold tracking-[0.15em] uppercase text-[#d9c391]">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-2 text-lg italic text-[#b8a073]">{{ $user->job_title }}</p>
                    @endif
                    <p class="mt-4 text-sm text-[#9c8a62] font-sans">
                        {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? '')), $user->phone, $user->email])->filter()->implode('  ✳  ') }}
                    </p>
                </div>
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-9">
                <h2 class="text-center text-xs font-bold tracking-[0.4em] uppercase text-[#c9a96a]">— Ansøgning —</h2>
                <div class="mt-6 space-y-4 leading-loose text-justify text-[15px] text-[#d6cbae]">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-9">
                <h2 class="text-center text-xs font-bold tracking-[0.4em] uppercase text-[#c9a96a]">— Erhvervserfaring &amp; uddannelse —</h2>
                <div class="mt-7 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="border border-[#4a4130] bg-[#2e291f] px-7 py-5">
                            <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                <h3 class="text-lg font-bold text-[#e8dcb9]">{{ $job['title'] }}</h3>
                                <p class="text-xs font-bold tracking-widest text-[#c9a96a] whitespace-nowrap tabular-nums font-sans">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-sm italic text-[#b8a073]">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-[#c9bc9c] font-sans">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-center text-xs font-bold tracking-[0.4em] uppercase text-[#c9a96a]">— Kompetencer —</h2>
                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-xs font-bold uppercase tracking-widest text-[#d9c391] border border-[#a8894f] px-3.5 py-1.5 font-sans">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-10 text-center text-[10px] tracking-[0.4em] uppercase text-[#7a6947] font-sans">
            {{ collect([$user->name, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ✳  ') }}
        </footer>
    </div>
</body>
</html>
