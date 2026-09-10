<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ auth()->user()->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4; margin: 0; }
        @media print {
            body { background: white !important; padding: 0 !important; }
            .cv-page { box-shadow: none !important; margin: 0 !important; max-width: 100% !important; }
        }
        .cv-page { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .cursor-blink { animation: none; }
    </style>
</head>
@php
    $user = auth()->user();
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->image ? '/storage/' . $user->image : $user->avatar;
@endphp
<body class="bg-white min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-black text-green-400 shadow-lg overflow-hidden min-h-[297mm] font-mono px-10 py-10 flex flex-col">

        {{-- Terminal header --}}
        <header class="border border-green-500/60 px-6 py-5">
            <p class="text-xs text-green-600">user@cv:~$ cat {{ strtolower(preg_replace('/[^a-z0-9]/i', '', $user->name ?? '')) }}.txt</p>
            <div class="mt-4 flex items-center gap-6">
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-20 h-20 object-cover border border-green-500/60 grayscale contrast-125 shrink-0">
                @endif
                <div>
                    <h1 class="text-3xl font-bold text-green-300 tracking-tight">{{ $user->name }}</h1>
                    @if ($user->job_title)
                        <p class="mt-1 text-sm text-green-500">// {{ $user->job_title }}</p>
                    @endif
                </div>
            </div>
            <div class="mt-4 text-xs space-y-1 text-green-500">
                <p><span class="text-green-300">phone:</span> {{ $user->phone ?? 'null' }}</p>
                <p><span class="text-green-300">email:</span> {{ $user->email }}</p>
                <p><span class="text-green-300">addr:</span> {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') ?: 'null' }}</p>
                @if ($user->birthdate)
                    <p><span class="text-green-300">born:</span> {{ $user->birthdate->format('d/m/Y') }}</p>
                @endif
            </div>
        </header>

        {{-- Kompetencer --}}
        @if ($skills)
            <section class="mt-6">
                <p class="text-xs text-green-600">user@cv:~$ grep skills</p>
                <div class="mt-2 border border-green-500/60 px-6 py-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="text-xs font-bold text-black bg-green-400 px-2 py-1">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Erhvervserfaring som commits --}}
        @if ($jobs)
            <section class="mt-6 flex-1">
                <p class="text-xs text-green-600">user@cv:~$ git log --experience</p>
                <div class="mt-2 border border-green-500/60 divide-y divide-green-500/25 divide-dashed">
                    @foreach ($jobs as $job)
                        <article class="px-6 py-4">
                            <p class="text-[10px] text-green-600">
                                commit {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                → {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'HEAD' }}
                            </p>
                            <h3 class="mt-1 font-bold text-green-300">{{ $job['title'] }}</h3>
                            <p class="text-xs text-green-500">@{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="mt-2 text-xs leading-relaxed text-green-400/80">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-6 text-xs text-green-600">
            <span class="text-green-300">user@cv:~$</span> exit <span class="text-green-500">_</span>
        </footer>
    </div>
</body>
</html>