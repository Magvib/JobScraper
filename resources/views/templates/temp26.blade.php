{{-- Clean Professional --}}
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
    </style>
</head>
@php
    $user = auth()->user();
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = is_string($user->skills) ? json_decode($user->skills, true) : ($user->skills ?? []);
    $photo = $user->getImage();
@endphp
<body class="bg-white min-h-screen py-8 px-4">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-lg min-h-[297mm] font-sans text-slate-800 px-14 py-12">

        {{-- Ren header --}}
        <header class="flex items-start gap-8 pb-8 border-b border-slate-200">
            <div class="flex-1">
                <h1 class="text-4xl font-bold text-slate-900 tracking-tight">{{ $user->name }}</h1>
                @if ($user->job_title)
                    <p class="mt-1.5 text-lg text-blue-600 font-medium">{{ $user->job_title }}</p>
                @endif
                <div class="mt-4 space-y-1.5 text-sm text-slate-500">
                    @if ($user->phone)
                        <p>📞 {{ $user->phone }}</p>
                    @endif
                    <p class="break-all">✉️ {{ $user->email }}</p>
                    @if ($user->address || $user->city)
                        <p>📍 {{ collect([$user->address, trim(($user->zip ?? '') . ' ' . ($user->city ?? ''))])->filter()->implode(', ') }}</p>
                    @endif
                    @if ($user->birthdate)
                        <p>🎂 {{ $user->birthdate->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $user->name }}"
                     class="w-28 h-28 rounded-full object-cover border-4 border-slate-100 shrink-0">
            @endif
        </header>

        {{-- Erhvervserfaring --}}
        @if ($jobs)
            <section class="mt-8">
                <h2 class="text-base font-bold text-slate-900 uppercase tracking-wide border-b-2 border-blue-600 pb-2">Erhvervserfaring &amp; Uddannelse</h2>
                <div class="mt-6 space-y-6">
                    @foreach ($jobs as $job)
                        <article>
                            <div class="flex items-baseline justify-between gap-4 flex-wrap">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $job['title'] }}</h3>
                                <p class="text-sm font-medium text-blue-600">
                                    {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                    –
                                    {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                                </p>
                            </div>
                            <p class="text-sm text-slate-500">{{ $job['company'] }}</p>
                            @if (!empty($job['description']))
                                <ul class="mt-2 space-y-1 text-sm text-slate-600 list-disc list-inside">
                                    <li>{{ $job['description'] }}</li>
                                </ul>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Kompetencer --}}
        @if ($skills)
            <section class="mt-10">
                <h2 class="text-base font-bold text-slate-900 uppercase tracking-wide border-b-2 border-blue-600 pb-2">Kompetencer</h2>
                <div class="mt-5 flex flex-wrap gap-2.5">
                    @foreach ($skills as $skill)
                        <span class="text-sm font-medium text-blue-700 bg-blue-50 border border-blue-100 rounded-md px-3.5 py-1.5">{{ $skill }}</span>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</body>
</html>