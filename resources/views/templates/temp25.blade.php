{{-- Polaroid Scrapbook --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ $user->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4; margin: 0; }
        @media print {
            body { background: white !important; padding: 0 !important; }
            .cv-page { box-shadow: none !important; margin: 0 !important; max-width: 100% !important; }
        }
        .cv-page { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .handwritten { font-family: 'Bradley Hand', 'Comic Sans MS', 'Segoe Print', cursive; }
        .tape {
            position: absolute;
            width: 70px; height: 22px;
            background: rgba(250, 240, 200, 0.75);
            border: 1px solid rgba(180, 160, 100, 0.3);
            box-shadow: 0 1px 2px rgba(0,0,0,0.12);
        }
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-amber-50 min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-linear-to-br from-orange-50 via-amber-50 to-lime-50 shadow-lg min-h-[297mm] px-12 py-12 handwritten text-stone-800 relative overflow-hidden">

        {{-- Decorative tape på siden --}}
        <div class="absolute top-6 left-24 rotate-[-6deg]"></div>

        {{-- Titel --}}
        <h1 class="text-4xl font-bold text-stone-700 rotate-[-1deg]">{{ $user->name }}</h1>
        @if ($user->job_title)
            <p class="mt-1 text-lg text-stone-500 rotate-[-1deg]">{{ $user->job_title }}</p>
        @endif

        <div class="mt-8 grid grid-cols-[70mm_1fr] gap-10 items-start">
            {{-- Polaroid-foto --}}
            <div class="bg-white px-3 pt-3 pb-10 shadow-lg rotate-3 relative">
                <span class="tape -top-2 left-1/2 -translate-x-1/2 rotate-[2deg]"></span>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}" class="w-full h-[80mm] object-cover">
                @else
                    <div class="w-full h-[80mm] bg-stone-200 flex items-center justify-center text-4xl">📷</div>
                @endif
                <p class="mt-3 text-center text-sm text-stone-500">mig på jobbet ✏️</p>
            </div>

            {{-- Kontaktkort --}}
            <div class="space-y-4 text-base">
                <div class="bg-white shadow-md px-5 py-4 rotate-[-1deg]">
                    <p>📱 {{ $user->phone ?? '—' }}</p>
                    <p class="break-all">✉️ {{ $user->email }}</p>
                </div>
                @if ($user->address || $user->city)
                    <div class="bg-white shadow-md px-5 py-4 rotate-[1deg]">
                        <p>🏠 {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                        @if ($user->birthdate)
                            <p class="mt-1">🎂 {{ $user->birthdate->format('d/m/Y') }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Erhvervserfaring som index-kort --}}
        @if ($jobs)
            <h2 class="mt-10 text-2xl font-bold text-stone-700 rotate-[-0.5deg] underline decoration-wavy decoration-orange-300 underline-offset-8">Erhvervserfaring &amp; Uddannelse</h2>
            <div class="mt-6 space-y-5">
                @foreach ($jobs as $i => $job)
                    <div class="bg-white shadow-md px-6 py-5 {{ $i % 2 === 0 ? 'rotate-[-0.7deg]' : 'rotate-[0.7deg]' }} relative
                                {{ $i % 3 === 0 ? 'border-l-4 border-l-orange-300' : ($i % 3 === 1 ? 'border-l-4 border-l-lime-300' : 'border-l-4 border-l-sky-300') }}">
                        <span class="tape {{ $i % 2 === 0 ? '-top-2 left-10 rotate-[3deg]' : '-top-2 right-10 rotate-[-3deg]' }}"></span>
                        <p class="text-sm text-stone-400">
                            ✏️ {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }} – {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                        </p>
                        <h3 class="mt-1 text-lg font-bold">{{ $job['title'] }}</h3>
                        <p class="text-stone-500">hos {{ $job['company'] }}</p>
                        @if (!empty($job['description']))
                            <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $job['description'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Kompetencer som klistermærker --}}
        @if ($skills)
            <h2 class="mt-10 text-2xl font-bold text-stone-700 rotate-[-0.5deg] underline decoration-wavy decoration-orange-300 underline-offset-8">Kompetencer</h2>
            <div class="mt-5 flex flex-wrap gap-3">
                @foreach ($skills as $i => $skill)
                    <span class="handwritten bg-white shadow px-4 py-2 text-sm font-bold
                                 {{ $i % 4 === 0 ? 'rotate-[-3deg] text-orange-500' : ($i % 4 === 1 ? 'rotate-[2deg] text-lime-600' : ($i % 4 === 2 ? 'rotate-[-2deg] text-sky-600' : 'rotate-[3deg] text-pink-500')) }}">
                        {{ $skill }} ✨
                    </span>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>