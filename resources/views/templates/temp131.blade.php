{{-- Table Professional --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-slate-800 px-14 py-13">

        {{-- Stram header over kraftig dobbeltlinje --}}
        <header class="pb-7 border-b-4 border-slate-900 flex items-start justify-between gap-8">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-base font-semibold text-slate-500">{{ $user->job_title }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover shrink-0 border border-slate-300">
            @endif
        </header>
        <p class="mt-4 text-sm text-slate-600">
            {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
        </p>

        @if (isset($coverLetter))
            <section class="mt-9">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900 border-b border-slate-300 pb-2">Ansøgning</h2>
                <div class="mt-6 space-y-4 text-[15px] leading-relaxed text-slate-700">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-9">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900 border-b border-slate-300 pb-2 mb-5">Erhvervserfaring &amp; uddannelse</h2>

                {{-- Erfaring som ren tabel --}}
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-left text-[10px] uppercase tracking-widest text-slate-500">
                            <th class="border border-slate-300 px-3 py-2 font-bold w-[68px]">Periode</th>
                            <th class="border border-slate-300 px-3 py-2 font-bold w-[150px]">Stilling</th>
                            <th class="border border-slate-300 px-3 py-2 font-bold w-[120px]">Arbejdsgiver</th>
                            <th class="border border-slate-300 px-3 py-2 font-bold">Beskrivelse</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jobs as $job)
                            <tr class="align-top">
                                <td class="border border-slate-300 px-3 py-3 tabular-nums text-slate-600 whitespace-nowrap text-xs font-semibold">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </td>
                                <td class="border border-slate-300 px-3 py-3 font-bold text-slate-900">{{ $job['title'] }}</td>
                                <td class="border border-slate-300 px-3 py-3 text-slate-600">{{ $job['company'] }}</td>
                                <td class="border border-slate-300 px-3 py-3 text-slate-600 leading-relaxed">
                                    {{ $job['description'] ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900 border-b border-slate-300 pb-2 mb-5">Kompetencer</h2>
                <table class="w-full text-sm border-collapse">
                    <tbody>
                        <tr>
                            <td class="border border-slate-300 px-3 py-2.5 bg-slate-100 text-[10px] uppercase tracking-widest text-slate-500 font-bold w-[120px]">Kompetencer</td>
                            <td class="border border-slate-300 px-3 py-2.5 text-slate-700 leading-relaxed">
                                {{ implode('  ·  ', $skills) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-9">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900 border-b border-slate-300 pb-2 mb-5">Links</h2>
                <table class="w-full text-sm border-collapse">
                    <tbody>
                        @foreach ($links as $link)
                            <tr>
                                <td class="border border-slate-300 px-3 py-2.5 bg-slate-100 text-[10px] uppercase tracking-widest text-slate-500 font-bold w-[120px]">{{ $link->name }}</td>
                                <td class="border border-slate-300 px-3 py-2.5 text-slate-700 break-all">
                                    <a href="{{ $link->url }}" class="underline">{{ $link->prettifyUrl() }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        <footer class="mt-12 pt-4 border-t-4 border-slate-900 flex justify-between text-xs text-slate-400 tracking-wide">
            <span>{{ $user->name }}</span>
            <span>{{ now()->format('Y') }}</span>
        </footer>
    </div>
</body>
</html>