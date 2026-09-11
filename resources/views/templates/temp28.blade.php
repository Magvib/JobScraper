{{-- Swiss Poster --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ $user->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .cv-page { box-shadow: none !important; margin: 0 !important; max-width: 100% !important; }
        }
        .cv-page { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg overflow-hidden min-h-[297mm] font-sans text-neutral-900 flex flex-col">

        {{-- Enorm farveblok med ét kæmpe ord --}}
        <header class="bg-red-600 text-white px-10 pt-16 pb-14">
            <div class="flex items-start justify-between gap-6">
                <h1 class="text-[64px] font-black uppercase tracking-tighter leading-[0.9]">{{ $user->name }}</h1>
                @if ($photo)
                    <img src="{{ $photo }}" alt="{{ $user->name }}"
                         class="w-24 h-24 object-cover grayscale contrast-125 shrink-0 mt-2">
                @endif
            </div>
            @if ($user->job_title)
                <p class="mt-4 text-lg font-medium tracking-wide">{{ $user->job_title }}</p>
            @endif
        </header>

        {{-- Kontakt som tynd linje --}}
        <div class="bg-neutral-900 text-white text-xs px-10 py-3 flex flex-wrap gap-x-6 gap-y-1 tracking-wider">
            @if ($user->phone)
                <span>{{ $user->phone }}</span>
            @endif
            <span class="break-all">{{ $user->email }}</span>
            @if ($user->address || $user->city)
                <span>{{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</span>
            @endif
            @if ($user->birthdate)
                <span>{{ $user->birthdate->format('d/m/Y') }}</span>
            @endif
        </div>

        <div class="flex-1 px-10 py-12 grid grid-cols-[1fr_52mm] gap-12">
            {{-- Erhvervserfaring i rent gitter --}}
            <main>
                <h2 class="text-2xl font-black uppercase tracking-tight border-b-4 border-neutral-900 pb-2">Erfaring</h2>
                <div class="mt-6 space-y-8">
                    @foreach ($jobs as $job)
                        <article class="grid grid-cols-[24mm_1fr] gap-4">
                            <p class="text-xs font-bold leading-5 text-red-600 uppercase">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}<br>—<br>{{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </p>
                            <div>
                                <h3 class="font-bold text-xl leading-tight">{{ $job['title'] }}</h3>
                                <p class="mt-1 text-sm text-neutral-500 uppercase tracking-wide text-xs">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="mt-2 text-sm leading-relaxed text-neutral-700">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </main>

            {{-- Kompetencer nummereret --}}
            @if ($skills)
                <aside>
                    <h2 class="text-2xl font-black uppercase tracking-tight border-b-4 border-neutral-900 pb-2">Skills</h2>
                    <ul class="mt-6 space-y-3">
                        @foreach ($skills as $i => $skill)
                            <li class="flex items-baseline gap-3 border-b border-neutral-200 pb-2">
                                <span class="text-xs font-black text-red-600">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="text-sm font-medium">{{ $skill }}</span>
                            </li>
                        @endforeach
                    </ul>
                </aside>
            @endif
        </div>
    </div>
</body>
</html>