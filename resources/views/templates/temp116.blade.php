{{-- Sumi Seal --}}
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
    $initials = collect(explode(' ', (string) $user->name))->filter()->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-[#faf9f7] shadow-lg min-h-[297mm] font-serif text-neutral-900 px-16 py-16 overflow-hidden relative">

        {{-- Rødt segl med initialer --}}
        <div class="absolute top-14 right-14 w-20 h-20 rounded-full border-2 border-red-700 flex items-center justify-center">
            <div class="w-16 h-16 rounded-full bg-red-700 flex items-center justify-center text-white text-2xl font-bold tracking-wider">
                {{ $initials }}
            </div>
        </div>

        <header class="pr-28 pb-9 border-b border-neutral-900">
            <p class="text-[10px] uppercase tracking-[0.5em] text-neutral-400">Curriculum Vitae</p>
            <h1 class="mt-4 text-5xl font-bold tracking-tight leading-none">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="mt-3 text-base text-neutral-500">{{ $user->job_title }}</p>
            @endif
        </header>

        <div class="mt-8 grid grid-cols-[1fr_150px] gap-12">
            <div>
                @if (isset($coverLetter))
                    <section>
                        <h2 class="text-sm font-bold uppercase tracking-[0.4em] text-neutral-900 flex items-center gap-4">
                            Ansøgning <span class="flex-1 h-px bg-neutral-300"></span>
                        </h2>
                        <div class="mt-6 space-y-4 text-[15px] leading-loose text-neutral-700">
                            {!! $coverLetter->renderContext() !!}
                        </div>
                    </section>
                @elseif ($jobs)
                    <section>
                        <h2 class="text-sm font-bold uppercase tracking-[0.4em] text-neutral-900 flex items-center gap-4">
                            Erhvervserfaring <span class="flex-1 h-px bg-neutral-300"></span>
                        </h2>
                        <div class="mt-6 space-y-7">
                            @foreach ($jobs as $job)
                                <article class="grid grid-cols-[95px_1fr] gap-5">
                                    <p class="text-[11px] text-neutral-400 tabular-nums pt-1.5 tracking-wider">
                                        {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>– {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                    </p>
                                    <div class="border-l border-neutral-300 pl-5">
                                        <h3 class="text-lg font-bold leading-snug">{{ $job['title'] }}</h3>
                                        <p class="text-sm text-neutral-500">{{ $job['company'] }}</p>
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
                    <section class="mt-10">
                        <h2 class="text-sm font-bold uppercase tracking-[0.4em] text-neutral-900 flex items-center gap-4">
                            Kompetencer <span class="flex-1 h-px bg-neutral-300"></span>
                        </h2>
                        <div class="mt-5 flex flex-wrap gap-2.5">
                            @foreach ($skills as $skill)
                                <span class="text-[13px] border border-neutral-400 px-3 py-1">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- Smal kolonne med foto og kontakt --}}
            <aside class="border-l border-neutral-300 pl-8 space-y-6">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full aspect-square object-cover grayscale">
                @endif
                <div class="text-xs leading-relaxed text-neutral-500">
                    @if ($user->phone)
                        <p class="mb-2">{{ $user->phone }}</p>
                    @endif
                    <p class="break-all mb-2">{{ $user->email }}</p>
                    @if ($user->address || $user->city)
                        <p class="mb-2 leading-snug">{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                    @endif
                    @if ($user->birthdate)
                        <p class="mb-2">Født {{ $user->birthdate->format('d/m/Y') }}</p>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</body>
</html>