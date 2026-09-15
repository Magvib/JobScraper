{{-- Type Specimen --}}
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
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md min-h-[297mm] font-sans text-neutral-900 px-14 py-12 overflow-hidden">

        {{-- Specimen-ark: typografiens skala som design --}}
        <header>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.45em] text-neutral-400">Type Specimen · {{ now()->format('Y') }}</p>
                    <h1 class="mt-6 text-8xl font-black tracking-tighter leading-[0.85]">{{ $user->name }}</h1>
                </div>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover grayscale-20 shrink-0">
                @endif
            </div>
            <div class="mt-6 flex items-baseline gap-6 flex-wrap border-t-2 border-neutral-900 pt-4">
                @if ($user->job_title)
                    <p class="text-2xl font-semibold tracking-tight">{{ $user->job_title }}</p>
                @endif
                <p class="text-sm text-neutral-500 ml-auto">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null, $user->birthdate ? 'Født ' . $user->birthdate->format('d/m/Y') : null])->filter()->implode('  ·  ') }}
                </p>
            </div>
        </header>

        @if (isset($coverLetter))
            <section class="mt-10">
                <div class="flex items-baseline justify-between border-b border-neutral-300 pb-1.5">
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em]">Ansøgning</h2>
                    <span class="font-mono text-[10px] text-neutral-400">regular · 15/26</span>
                </div>
                <div class="mt-5 space-y-4 text-[15px] leading-[1.65] text-neutral-700">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-10">
                <div class="flex items-baseline justify-between border-b border-neutral-300 pb-1.5">
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em]">Erhvervserfaring &amp; uddannelse</h2>
                    <span class="font-mono text-[10px] text-neutral-400">bold · 18/1.4</span>
                </div>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article class="grid grid-cols-[100px_1fr] gap-6 border-b border-neutral-200 pb-6">
                            <p class="font-mono text-[11px] font-bold text-neutral-400 tabular-nums uppercase pt-1.5 text-right">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <div>
                                <h3 class="text-2xl font-bold tracking-tight leading-tight">{{ $job['title'] }}</h3>
                                <p class="mt-1 text-sm font-medium text-neutral-500 italic">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-2 leading-relaxed text-neutral-600">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-8">
                <div class="flex items-baseline justify-between border-b border-neutral-300 pb-1.5">
                    <h2 class="text-xs font-bold uppercase tracking-[0.3em]">Kompetencer</h2>
                    <span class="font-mono text-[10px] text-neutral-400">medium · 13/1</span>
                </div>
                <div class="mt-4 flex flex-wrap gap-x-7 gap-y-2">
                    @foreach ($skills as $skill)
                        <span class="text-[13px] font-medium text-neutral-700">{{ $skill }}<span class="text-neutral-300">.</span></span>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-12 flex justify-between items-end">
            <p class="text-[10px] font-mono uppercase tracking-widest text-neutral-400">AaBbCcDdEeFfGg · 0123456789</p>
            <p class="text-5xl font-black text-neutral-200 leading-none select-none">Aa</p>
        </footer>
    </div>
</body>
</html>