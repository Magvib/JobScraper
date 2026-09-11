{{-- Classic Serif --}}
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CV - {{ $user->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body { background: white; padding: 0 !important; }
            .cv-page { box-shadow: none !important; margin: 0 !important; border-radius: 0 !important; max-width: 100% !important; }
        }
    </style>
</head>
@php
    $jobs = $user->cv_json ? json_decode($user->cv_json, true) : [];
    $skills = $user->skills ?? [];
@endphp
<body class="bg-white min-h-screen">
    <div class="cv-page max-w-[210mm] mx-auto bg-white shadow-md px-16 py-14 font-serif text-gray-900">
        {{-- Navn og kontakt --}}
        <header class="text-center pb-6 border-b-2 border-gray-900">
            <h1 class="text-3xl font-bold tracking-wide uppercase">{{ $user->name }}</h1>
            @if ($user->job_title)
                <p class="text-lg mt-1 text-gray-700">{{ $user->job_title }}</p>
            @endif
            <p class="text-sm mt-3 text-gray-700">
                {{ collect([$user->address, $user->zip . ' ' . $user->city, $user->phone, $user->email])->filter()->implode(' · ') }}
            </p>
        </header>

        {{-- Erhvervserfaring & uddannelse --}}
        @if ($jobs)
            <section class="mt-8">
                <h2 class="text-sm font-bold uppercase tracking-widest border-b border-gray-900 pb-1">Erhvervserfaring &amp; uddannelse</h2>
                <div class="mt-5 space-y-5">
                    @foreach ($jobs as $job)
                        <div class="grid grid-cols-[110px_1fr] gap-4">
                            <div class="text-sm text-gray-600 pt-0.5">
                                {{ \Carbon\Carbon::parse($job['startDate'])->format('m/Y') }}
                                –
                                {{ !empty($job['endDate']) ? \Carbon\Carbon::parse($job['endDate'])->format('m/Y') : 'nu' }}
                            </div>
                            <div>
                                <h3 class="font-bold leading-snug">{{ $job['title'] }}</h3>
                                <p class="text-sm italic text-gray-600">{{ $job['company'] }}</p>
                                @if (!empty($job['description']))
                                    <p class="text-sm mt-1.5 leading-relaxed text-justify">{{ $job['description'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Kompetencer --}}
        @if ($skills)
            <section class="mt-8">
                <h2 class="text-sm font-bold uppercase tracking-widest border-b border-gray-900 pb-1">Kompetencer</h2>
                <p class="mt-3 text-sm">{{ implode(', ', $skills) }}</p>
            </section>
        @endif

        {{-- Personlige oplysninger --}}
        <section class="mt-8">
            <h2 class="text-sm font-bold uppercase tracking-widest border-b border-gray-900 pb-1">Personlige oplysninger</h2>
            <dl class="mt-3 grid grid-cols-[140px_1fr] gap-y-1.5 text-sm">
                @if ($user->birthdate)
                    <dt class="font-semibold">Fødselsdato</dt>
                    <dd>{{ $user->birthdate->format('d/m Y') }}</dd>
                @endif
                <dt class="font-semibold">Adresse</dt>
                <dd>{{ collect([$user->address, $user->zip, $user->city])->filter()->implode(', ') }}</dd>
                @if ($user->phone)
                    <dt class="font-semibold">Telefon</dt>
                    <dd>{{ $user->phone }}</dd>
                @endif
                <dt class="font-semibold">E-mail</dt>
                <dd>{{ $user->email }}</dd>
            </dl>
        </section>
    </div>
</body>
</html>