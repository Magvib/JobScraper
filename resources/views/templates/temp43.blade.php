{{-- Slate Harmony --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-slate-800">

        {{-- Lys slate header --}}
        <header class="bg-slate-100 px-12 py-10 flex items-center gap-8 border-b-4 border-slate-700">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-24 h-24 object-cover rounded-lg shrink-0 border-2 border-slate-700">
            @endif
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1 text-lg text-slate-600">{{ $user->job_title }}</p>
                @endif
                <p class="mt-3 text-sm text-slate-600">
                    {{ collect([$user->phone, $user->email, $user->address ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </p>
            </div>
        </header>

        <div class="px-12 py-10">
            @if (isset($coverLetter))
                <section>
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-500">Ansøgning</h2>
                    <div class="mt-5 space-y-4 text-[15px] leading-relaxed border-l-4 border-slate-300 pl-6">
                        {!! $coverLetter->renderContext() !!}
                    </div>
                </section>
            @elseif ($jobs)
                <section>
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-500">Erhvervserfaring &amp; uddannelse</h2>
                    <div class="mt-6 divide-y divide-slate-200">
                        @foreach ($jobs as $job)
                            <article class="py-5 first:pt-0 grid grid-cols-[110px_1fr] gap-6">
                                <p class="text-sm font-semibold text-slate-500 pt-0.5">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                                <div>
                                    <h3 class="text-lg font-bold leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                    <p class="text-sm text-slate-500 font-medium">{{ $job['company'] }}</p>
                                    @if (!empty($job['description']))
                                        <p class="text-sm mt-2 leading-relaxed text-slate-700">{{ $job['description'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($skills)
                <section class="mt-10">
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-500">Kompetencer</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <span class="text-sm font-medium text-slate-700 bg-slate-100 border border-slate-300 px-3 py-1 rounded">{{ $skill }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($links->isNotEmpty())
                <section class="mt-10">
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-500">Links</h2>
                    <ul class="mt-4 space-y-2 text-sm">
                        @foreach ($links as $link)
                            <li class="flex flex-wrap gap-x-2">
                                <span class="font-medium text-slate-700">{{ $link->name }}:</span>
                                <a href="{{ $link->url }}" class="text-slate-600 underline break-all">{{ $link->prettifyUrl() }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="mt-10 pt-6 border-t border-slate-200">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-slate-500">Personlige oplysninger</h2>
                <dl class="mt-3 grid grid-cols-2 gap-y-1.5 gap-x-8 text-sm">
                    @if ($user->birthdate)
                        <div class="flex gap-2"><dt class="font-semibold text-slate-600">Fødselsdato:</dt><dd>{{ $user->birthdate->format('d/m/Y') }}</dd></div>
                    @endif
                    <div class="flex gap-2"><dt class="font-semibold text-slate-600">Adresse:</dt><dd>{{ collect([$user->address, $user->zip, $user->city])->filter()->implode(', ') }}</dd></div>
                    @if ($user->phone)
                        <div class="flex gap-2"><dt class="font-semibold text-slate-600">Telefon:</dt><dd>{{ $user->phone }}</dd></div>
                    @endif
                    <div class="flex gap-2"><dt class="font-semibold text-slate-600">E-mail:</dt><dd class="break-all">{{ $user->email }}</dd></div>
                </dl>
            </section>
        </div>
    </div>
</body>
</html>
