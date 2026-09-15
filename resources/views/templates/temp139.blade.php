{{-- Sticky Board --}}
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
        /* Blødgjort kork-agtig baggrund */
        .board {
            background:
                radial-gradient(circle at 20% 30%, rgba(120, 113, 108, 0.08) 0 2px, transparent 3px),
                radial-gradient(circle at 70% 60%, rgba(120, 113, 108, 0.08) 0 2px, transparent 3px),
                radial-gradient(circle at 40% 85%, rgba(120, 113, 108, 0.08) 0 2px, transparent 3px),
                #eef1f4;
            background-size: 140px 140px, 90px 90px, 60px 60px, auto;
        }
        /* Post-it med let hængende bund */
        .sticky {
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.14), 0 1px 2px rgba(15, 23, 42, 0.10);
            clip-path: polygon(0 0, 100% 0, 100% 97%, 96% 100%, 0 100%);
        }
        /* Tegnestift **/
        .pin::before {
            content: '';
            position: absolute;
            top: 7px;
            left: 50%;
            transform: translateX(-50%);
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, #fecaca, #ef4444 60%, #991b1b);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
        }
    </style>
</head>
@php
    $jobs = $user->cv_json;
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
    $stickyColors = ['#fef3c7', '#dcfce7', '#dbeafe', '#fae8ff'];
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page board max-w-[210mm] mx-auto shadow-xl min-h-[297mm] font-sans text-slate-800 px-12 py-12 overflow-hidden">

        {{-- Navnekort tegnet fast på tavlen --}}
        <header class="sticky pin relative bg-white rounded-lg px-8 py-6 flex items-center justify-between gap-8 w-[165mm] mx-auto">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-base font-semibold text-slate-500">{{ $user->job_title }}</p>
                @endif
                <p class="mt-3 text-sm text-slate-500">
                    {{ collect([$user->phone, $user->email, $user->address || $user->city ? collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') : null])->filter()->implode('  ·  ') }}
                </p>
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-24 h-24 object-cover shrink-0 rounded-lg border-4 border-white shadow-md rotate-2">
            @endif
        </header>

        @if (isset($coverLetter))
            <section class="sticky bg-white relative rounded-md px-9 py-8 mt-9 w-[165mm] mx-auto">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.3em] text-slate-900 border-b-2 border-slate-900 pb-2">Ansøgning</h2>
                <div class="mt-5 space-y-4 text-[15px] leading-relaxed text-slate-700">
                    {!! $coverLetter->renderContext() !!}
                </div>
            </section>
        @elseif ($jobs)
            <section class="mt-9">
                <h2 class="sticky bg-slate-800 text-white inline-block px-5 py-2.5 text-xs font-extrabold uppercase tracking-[0.25em] rounded rotate-[-1deg] shadow-md ml-6">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-6 grid grid-cols-2 gap-7 px-4">
                    @foreach ($jobs as $i => $job)
                        <article class="sticky pin relative rounded-md px-6 py-5"
                                 style="background: {{ $stickyColors[$i % count($stickyColors)] }}">
                            <p class="text-[11px] font-bold tracking-widest text-slate-500 tabular-nums uppercase">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} — {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <h3 class="mt-1.5 font-extrabold text-lg leading-snug text-slate-900">{{ $job['title'] }}</h3>
                            <p class="text-sm font-semibold text-slate-600">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <p class="text-sm mt-2.5 leading-relaxed text-slate-700">{{ $job['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($skills)
            <section class="mt-10">
                <h2 class="sticky bg-slate-800 text-white inline-block px-5 py-2.5 text-xs font-extrabold uppercase tracking-[0.25em] rounded rotate-[1deg] shadow-md ml-6">Kompetencer</h2>
                <div class="mt-6 flex flex-wrap gap-4 px-6">
                    @foreach ($skills as $i => $skill)
                        <span class="sticky inline-block text-sm font-bold text-slate-800 px-4 py-2.5 rounded {{ ['rotate-1', '-rotate-1', 'rotate-2', '-rotate-2'][$i % 4] }}"
                              style="background: {{ $stickyColors[($i + 1) % count($stickyColors)] }}">
                            {{ $skill }}
                        </span>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="mt-12 text-center text-xs text-slate-400 tracking-wide">
            @if ($user->birthdate)
                Født {{ $user->birthdate->format('d/m/Y') }}  ·
            @endif
            {{ $user->name }}
        </footer>
    </div>
</body>
</html>