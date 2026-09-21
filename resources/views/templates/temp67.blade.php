{{-- Signature Script --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($coverLetter) ? 'Letter - ' : 'CV - ' }}{{ $user->name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap');
        @media print {
            body { background: white !important; padding: 0 !important; }
            .cv-page { box-shadow: none !important; margin: 0 !important; max-width: 100% !important; }
        }
        .signature { font-family: 'Great Vibes', cursive; }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $links = $user->links;
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md px-16 py-14 font-serif text-gray-800 min-h-[297mm]">

        {{-- Signatur header --}}
        <header class="flex items-end justify-between gap-8 pb-6 border-b border-gray-300">
            <div>
                <p class="signature text-5xl text-indigo-900">{{ $user->name }}</p>
                @if ($user->job_title)
                    <p class="mt-1 text-sm uppercase tracking-[0.25em] text-gray-500">{{ $user->job_title }}</p>
                @endif
            </div>
            <div class="text-right text-xs text-gray-500 space-y-0.5">
                @if ($user->phone)
                    <p>{{ $user->phone }}</p>
                @endif
                <p class="break-all">{{ $user->email }}</p>
                @if ($user->address || $user->city)
                    <p>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                @endif
                @if ($user->birthdate)
                    <p>Født {{ $user->birthdate->format('d/m/Y') }}</p>
                @endif
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="hidden">
            @endif
        </header>

        @if (isset($coverLetter))
            <section class="mt-8">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-indigo-900">Ansøgning</h2>
                <div class="mt-5 space-y-4 leading-relaxed text-[15px]">
                    {!! $coverLetter->renderContext() !!}
                </div>
                <p class="signature text-3xl mt-8 text-indigo-900 text-right">{{ $user->name }}</p>
            </section>
        @elseif ($jobs)
            <section class="mt-8">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-indigo-900">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="pl-6 border-l border-indigo-200">
                            <div class="flex items-baseline justify-between gap-3 flex-wrap">
                                <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-xs text-gray-500 whitespace-nowrap tabular-nums">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-sm italic text-indigo-800">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2 leading-relaxed text-gray-600">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-9">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-indigo-900">Kompetencer</h2>
                <p class="mt-3 text-sm text-gray-600 leading-loose">{{ implode('  ·  ', $skills) }}</p>
            </section>
        @endif

        @if ($links->isNotEmpty())
            <section class="mt-9">
                <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-indigo-900">Links</h2>
                <ul class="mt-3 space-y-1.5 text-sm">
                    @foreach ($links as $link)
                        <li>
                            <span class="font-semibold text-gray-700">{{ $link->name }}:</span>
                            <a href="{{ $link->url }}" class="text-indigo-800 underline break-all">{{ $link->prettifyUrl() }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</body>
</html>
