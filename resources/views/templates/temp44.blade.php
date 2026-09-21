{{-- Copper Ledger --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-[#fdfbf7] shadow-lg min-h-[297mm] font-serif text-stone-900 px-14 py-12">

        {{-- Kobber header --}}
        <header class="text-center">
            <div class="flex items-center justify-center gap-4 text-amber-700">
                <span class="h-px w-16 bg-amber-700"></span>
                <span class="text-[10px] font-bold uppercase tracking-[0.5em]">Curriculum Vitae</span>
                <span class="h-px w-16 bg-amber-700"></span>
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover rounded-full mx-auto mt-6 border-2 border-amber-700 p-0.5">
            @endif
            <h1 class="mt-4 text-4xl font-bold tracking-wide">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-1 text-lg italic text-amber-800">{{ $user->job_title }}</p>
            @endif
            <p class="mt-3 text-sm text-stone-600">
                {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? '')), $user->phone, $user->email])->filter()->implode(' · ') }}
            </p>
            <div class="mt-5 border-t-2 border-b border-amber-700 py-0.5"><div class="border-b-2 border-amber-700"></div></div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-9">
                <h2 class="text-center text-xs font-bold uppercase tracking-[0.4em] text-amber-800">— Ansøgning —</h2>
                <div class="mt-6 space-y-4 leading-relaxed text-justify text-[15px]">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-9">
                <h2 class="text-center text-xs font-bold uppercase tracking-[0.4em] text-amber-800">— Erhvervserfaring &amp; uddannelse —</h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="text-center">
                            <h3 class="text-lg font-bold">{{ $job['title'] }}</h3>
                            <p class="text-sm italic text-amber-800">
                                {{ $job['company'] }}
                                <span class="text-stone-500 not-italic">
                                    · {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </span>
                            </p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-stone-700 max-w-xl mx-auto">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-center text-xs font-bold uppercase tracking-[0.4em] text-amber-800">— Kompetencer —</h2>
                <p class="mt-4 text-sm text-center leading-loose">
                    {{ implode('  ·  ', $skills) }}
                </p>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-9">
                <h2 class="text-center text-xs font-bold uppercase tracking-[0.4em] text-amber-800">— Links —</h2>
                <ul class="mt-4 text-sm text-center space-y-1 max-w-lg mx-auto">
                    @foreach ($links as $link)
                        <li>
                            <span class="font-bold">{{ $link->name }}</span>
                            <span class="text-amber-800"> · </span>
                            <a href="{{ $link->url }}" class="text-stone-700 underline break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="mt-9">
            <h2 class="text-center text-xs font-bold uppercase tracking-[0.4em] text-amber-800">— Personlige oplysninger —</h2>
            <dl class="mt-4 grid grid-cols-2 gap-y-2 gap-x-8 text-sm max-w-lg mx-auto">
                @if ($user->birthdate)
                    <dt class="font-bold text-right">Fødselsdato</dt>
                    <dd>{{ $user->birthdate->format('d/m/Y') }}</dd>
                @endif
                <dt class="font-bold text-right">Adresse</dt>
                <dd>{{ collect([$user->address, $user->zip, $user->city])->filter()->implode(', ') }}</dd>
                @if ($user->phone)
                    <dt class="font-bold text-right">Telefon</dt>
                    <dd>{{ $user->phone }}</dd>
                @endif
                <dt class="font-bold text-right">E-mail</dt>
                <dd class="break-all">{{ $user->email }}</dd>
            </dl>
        </section>

        <footer class="mt-10 border-t-2 border-amber-700 pt-3 text-center text-[10px] uppercase tracking-[0.4em] text-stone-500">
            {{ $user->name }}
        </footer>
    </div>
</body>
</html>
