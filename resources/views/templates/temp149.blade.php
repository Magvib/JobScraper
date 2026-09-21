{{-- Slant Labels --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-slate-800 px-14 py-14">

        <header class="flex items-end justify-between gap-8 pb-8">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-base font-semibold text-rose-500">{{ $user->job_title }}</p>
                @endif
                <p class="mt-4 text-sm text-slate-500">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </p>
            </div>
            @if ($photo)
                <div class="shrink-0 bg-rose-100 px-2 py-2 -skew-x-6">
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover skew-x-6 grayscale-20">
                </div>
            @endif
        </header>

        {{-- Parallelogram-etiketter for hver sektion --}}
        @if (isset($coverLetter))
            <section class="mt-4">
                <h2 class="inline-block bg-slate-900 text-white text-xs font-extrabold uppercase tracking-[0.25em] px-6 py-2.5 -skew-x-12">
                    <span class="inline-block skew-x-12">Ansøgning</span>
                </h2>
                <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-slate-700">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-4">
                <h2 class="inline-block bg-slate-900 text-white text-xs font-extrabold uppercase tracking-[0.25em] px-6 py-2.5 -skew-x-12">
                    <span class="inline-block skew-x-12">Erhvervserfaring &amp; uddannelse</span>
                </h2>
                <div class="mt-7 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="grid grid-cols-[110px_1fr] gap-6">
                            <p class="text-right text-xs font-extrabold text-rose-500 tabular-nums pt-1.5 uppercase tracking-wider">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>
                                <span class="text-slate-400">– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}</span>
                            </p>
                            <div class="border-l-2 border-slate-200 pl-5">
                                <h3 class="font-bold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                                <p class="text-sm font-medium text-slate-500">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-slate-600">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-10">
                <h2 class="inline-block bg-rose-500 text-white text-xs font-extrabold uppercase tracking-[0.25em] px-6 py-2.5 -skew-x-12">
                    <span class="inline-block skew-x-12">Kompetencer</span>
                </h2>
                <div class="mt-6 flex flex-wrap gap-2.5">
                    @foreach ($skills as $skill)
                        <span class="text-[13px] font-semibold text-slate-700 bg-slate-100 border border-slate-200 px-4 py-1.5 -skew-x-6">
                            <span class="inline-block skew-x-6">{{ $skill }}</span>
                        </span>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mt-10">
            <h2 class="inline-block bg-slate-900 text-white text-xs font-extrabold uppercase tracking-[0.25em] px-6 py-2.5 -skew-x-12">
                <span class="inline-block skew-x-12">Personligt</span>
            </h2>
            <dl class="mt-6 grid grid-cols-[130px_1fr] gap-y-2 text-sm">
                @if ($user->birthdate)
                    <dt class="font-semibold text-slate-500">Fødselsdato</dt>
                    <dd>{{ $user->birthdate->format('d/m/Y') }}</dd>
                @endif
                <dt class="font-semibold text-slate-500">E-mail</dt>
                <dd class="break-all">{{ $user->email }}</dd>
                @if ($user->address || $user->city)
                    <dt class="font-semibold text-slate-500">Adresse</dt>
                    <dd>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</dd>
                @endif
            </dl>
        </section>

        @if ($links->isNotEmpty())
            <section class="mt-10">
                <h2 class="inline-block bg-rose-500 text-white text-xs font-extrabold uppercase tracking-[0.25em] px-6 py-2.5 -skew-x-12">
                    <span class="inline-block skew-x-12">Links</span>
                </h2>
                <ul class="mt-6 text-sm space-y-1.5 text-slate-700">
                    @foreach ($links as $link)
                        <li>
                            <span class="font-semibold text-slate-900">{{ $link->name }}:</span>
                            <a href="{{ $link->url }}" class="text-rose-600 underline break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="mt-12 pt-4 border-t border-slate-200 flex justify-between text-xs text-slate-400 tracking-wide">
            <span>{{ $user->name }}</span>
            <span>{{ now()->format('Y') }}</span>
        </footer>
    </div>
</body>
</html>