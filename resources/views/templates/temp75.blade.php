{{-- Arctic Byte --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ $user->name }}</title>
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-mono text-gray-900 px-14 py-12">

        {{-- Sort/hvid med en enkelt isblå accent --}}
        <header class="border-b-2 border-gray-900 pb-6">
            <p class="text-xs text-sky-500 tracking-widest">// curriculum-vitae</p>
            <div class="mt-3 flex items-start justify-between gap-8">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1.5 text-gray-600">// {{ $user->job_title }}</p>
                    @endif
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-20 h-20 object-cover shrink-0 border-2 border-gray-900">
                @endif
            </div>
            <div class="mt-4 text-xs text-gray-600 space-y-0.5">
                @if ($user->phone)
                    <p><span class="text-sky-500">phone:</span> {{ $user->phone }}</p>
                @endif
                <p class="break-all"><span class="text-sky-500">email:</span> {{ $user->email }}</p>
                @if ($user->address || $user->city)
                    <p><span class="text-sky-500">addr:</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                @endif
                @if ($user->birthdate)
                    <p><span class="text-sky-500">born:</span> {{ $user->birthdate->format('d/m/Y') }}</p>
                @endif
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="text-sm font-bold bg-gray-900 text-white inline-block px-3 py-1.5">== ANSØGNING ==</h2>
                <div class="mt-5 space-y-4 text-sm leading-relaxed">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="text-sm font-bold bg-gray-900 text-white inline-block px-3 py-1.5">== ERHVERVSERFARING &amp; UDDANNELSE ==</h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="border border-gray-300">
                            <div class="bg-gray-50 border-b border-gray-300 px-4 py-2 flex items-baseline justify-between gap-3 flex-wrap">
                                <h3 class="font-bold text-sm">{{ $job['title'] }} <span class="font-normal text-gray-500">@ {{ $job['company'] }}</span></h3>
                                <p class="text-xs text-sky-600 font-bold whitespace-nowrap tabular-nums">
                                    [{{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} → {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}]
                                </p>
                            </div>
                            @if (!empty($job['description']))
                                <p class="px-4 py-3 text-xs leading-relaxed text-gray-700">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-8">
                <h2 class="text-sm font-bold bg-gray-900 text-white inline-block px-3 py-1.5">== KOMPETENCER ==</h2>
                <p class="mt-4 text-sm text-gray-700">
                    <span class="text-sky-500">[</span>{{ implode(', ', $skills) }}<span class="text-sky-500">]</span>
                </p>
            </section>
        @endif

        <footer class="mt-10 pt-4 border-t border-gray-300 text-center text-xs text-gray-500">
            // {{ $user->name }} — {{ now()->format('Y') }}
        </footer>
    </div>
</body>
</html>
