<?php

use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

new class extends Component
{
    public string $search = '';

    public ?string $selected = null;

    public array $templates = [];

    public array $letters = [];

    public function mount()
    {
        $this->letters = auth()->user()->coverLetters()->orderBy('created_at', 'desc')->pluck('title', 'id')->map(function ($title, $id) {
            return ['id' => $id, 'title' => $title];
        })->values()->toArray();
        
        $this->templates = collect(glob(resource_path('views/templates/temp*.blade.php')))
            ->map(function ($file) {
                $slug = basename($file, '.blade.php');

                // Template name lives in the first blade comment: {{-- Classic Serif --}}
                $name = $slug;
                $handle = fopen($file, 'r');
                if ($handle) {
                    $firstLine = (string) fgets($handle);
                    fclose($handle);
                    if (preg_match('/{{--\s*(.*?)\s*--}}/u', $firstLine, $m)) {
                        $name = $m[1];
                    }
                }

                return [
                    'slug' => $slug,
                    'name' => $name,
                    'number' => (int) preg_replace('/\D/', '', $slug),
                ];
            })
            ->sortBy('number')
            ->values()
            ->all();
    }

    public function getFilteredTemplatesProperty(): array
    {
        if ($this->search === '') {
            return $this->templates;
        }

        $needle = strtolower(trim($this->search));

        return array_values(array_filter(
            $this->templates,
            fn (array $template) => str_contains(strtolower($template['name']), $needle)
                || str_contains($template['slug'], $needle)
        ));
    }

    public function select(string $slug): void
    {
        $this->selected = $slug;
    }

    public function download(string $slug, ?int $coverLetterId = null)
    {
        $signedRoute = URL::temporarySignedRoute('signed-template', now()->addMinutes(2), array_filter([
            'slug' => $slug,
            'user' => auth()->id(),
            'coverLetter' => $coverLetterId,
        ]));
        $gotenbergApiUrl = config('services.gotenberg.api_url');
        $gotenbergApiBasicAuthUsername = config('services.gotenberg.basic_auth_username');
        $gotenbergApiBasicAuthPassword = config('services.gotenberg.basic_auth_password');

        $response = Http::withBasicAuth($gotenbergApiBasicAuthUsername, $gotenbergApiBasicAuthPassword)
            ->attach('url', $signedRoute)
            ->attach('paperWidth', '210mm')
            ->attach('singlePage', 'true')
            ->attach('marginTop', '0')
            ->attach('marginBottom', '0')
            ->attach('marginLeft', '0')
            ->attach('marginRight', '0')
            ->post("{$gotenbergApiUrl}/forms/chromium/convert/url");

        $pdf = $response->body();

        if (! str_starts_with($pdf, '%PDF')) {
            abort(500, 'Gotenberg did not return a PDF: ' . substr($pdf, 0, 500));
        }

        $user = auth()->user();
        $name = ($coverLetterId ? "letter-" . Str::slug($user->name) : "cv-" . Str::slug($user->name)) . '.pdf';
    
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf;
        }, $name, ['Content-Type' => 'application/pdf']);
    }

    public function close(): void
    {
        $this->selected = null;
    }
};
?>

<div class="py-10 mx-4">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold">Templates</h1>
                <p class="mt-2 opacity-70">
                    Showing {{ count($this->filteredTemplates) }} of {{ count($templates) }} templates — click one to preview
                </p>
            </div>
            <label class="input input-bordered flex items-center gap-2 w-full md:w-72">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-60" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
                <input type="text" class="grow" placeholder="Search templates..."
                    wire:model.live.debounce.300ms="search">
            </label>
        </div>

        @if (count($this->filteredTemplates) === 0)
            <div class="alert alert-warning">
                No templates match "{{ $search }}". Try another search.
            </div>
        @endif

        <div class="grid justify-center gap-6" style="grid-template-columns: repeat(auto-fill, 264px)">
            @foreach ($this->filteredTemplates as $template)
                <div wire:key="tpl-{{ $template['slug'] }}"
                    class="card bg-base-100 shadow-md hover:shadow-xl transition-shadow overflow-hidden">
                    <div class="cursor-pointer" wire:click="select('{{ $template['slug'] }}')">
                        <div class="cv-thumb" wire:ignore>
                            <iframe src="{{ route('template', $template['slug']) }}" loading="lazy" scrolling="no"
                                title="{{ $template['name'] }}"></iframe>
                        </div>
                    </div>
                    <div class="card-body py-3 px-4 flex-row items-center justify-between gap-2">
                        <div>
                            <h2 class="card-title text-base leading-tight">{{ $template['name'] }}</h2>
                            <p class="text-xs opacity-60">#{{ $template['number'] }}</p>
                        </div>
                        <button class="btn btn-sm btn-ghost" wire:click="download('{{ $template['slug'] }}')" title="Download PDF">
                            Download
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if ($selected)
        @php
            $firstLetterId = collect($letters)->first()['id'] ?? null;
        @endphp
        <div class="modal modal-open" wire:keydown.escape.window="close">
            <div class="modal-box max-w-7xl p-4 flex flex-col items-center gap-3"
                x-data="{ letterId: {{ $firstLetterId ?? 'null' }}, letters: @entangle('letters') }">
                @php
                    $selectedTemplate = collect($this->templates)->firstWhere('slug', $selected);
                @endphp
                <span class="card-title text-base leading-tight">
                    {{ $selectedTemplate['name'] ?? $selected }}
                </span>
                <div class="relative flex flex-col lg:flex-row items-start gap-3">
                    <div class="cv-modal-thumb" wire:key="modal-frame-{{ $selected }}" wire:ignore>
                        <iframe src="{{ route('template', $selected) }}" title="Preview"></iframe>
                    </div>
                    <div class="relative">
                        {{-- Switch between saved cover letters, floating over the letter preview --}}
                        <details class="dropdown dropdown-end absolute right-3 top-3 z-30">
                            <summary class="btn btn-outline btn-sm bg-base-100/90 backdrop-blur-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span x-text="letters.find(l => l.id == letterId)?.title ?? 'No cover letter'">Cover letter</span>
                            </summary>
                            <ul class="dropdown-content menu bg-base-100 rounded-box shadow-lg border border-base-300 w-64 z-30">
                                @forelse ($letters as $letter)
                                    <li wire:key="modal-letter-{{ $letter['id'] }}">
                                        <button @click="letterId = {{ $letter['id'] }}; $el.closest('details').removeAttribute('open')"
                                            :class="letterId == {{ $letter['id'] }} && 'text-primary'">
                                            {{ $letter['title'] ?? __('Untitled') }}
                                        </button>
                                    </li>
                                @empty
                                    <li class="disabled"><span class="opacity-60">No saved letters yet</span></li>
                                @endforelse
                            </ul>
                        </details>
                        <div class="cv-modal-thumb" wire:key="modal-letter-frame-{{ $selected }}" wire:ignore>
                            <iframe :src="'{{ route('template', ['name' => $selected]) }}' + (letterId ? '?coverLetter=' + letterId : '')"
                                title="Preview"></iframe>
                        </div>
                    </div>
                    {{-- Download CV / Download Letter, anchored to the bottom of the previews --}}
                    <details class="dropdown dropdown-top dropdown-center absolute bottom-3 left-1/2 -translate-x-1/2 z-30">
                        <summary class="btn btn-outline btn-sm bg-base-100/90 backdrop-blur-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download
                        </summary>
                        <ul class="dropdown-content menu bg-base-100 rounded-box shadow-lg border border-base-300 w-64 z-30">
                            <li>
                                <button @click="$el.closest('details').removeAttribute('open')"
                                    wire:click="download('{{ $selected }}')">
                                    Download CV
                                </button>
                            </li>
                            <li>
                                <button @click="$el.closest('details').removeAttribute('open')"
                                    wire:click="download('{{ $selected }}', letterId)">
                                    Download Letter
                                </button>
                            </li>
                        </ul>
                    </details>
                </div>
                <button class="btn btn-error w-full" wire:click="close">Close</button>
            </div>
            <div class="modal-backdrop bg-black/60" wire:click="close"></div>
        </div>
    @endif
</div>

<style>
    /* A4 at 96dpi is 794x1123px — scale each iframe down to a 264px-wide thumbnail */
    .cv-thumb {
        position: relative;
        width: 264px;
        height: 374px;
        overflow: hidden;
        background: #fff;
        transition: transform 0.15s ease;
        transform-origin: top left;
    }

    .cv-thumb iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 794px;
        height: 1123px;
        border: 0;
        transform: scale(0.333);
        transform-origin: top left;
        pointer-events: none; /* let the parent div handle clicks */
    }

    .cv-modal-thumb {
        position: relative;
        width: 596px;
        height: 843px;
        overflow: hidden;
        background: #fff;
        border-radius: 8px;
    }

    .cv-modal-thumb iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 794px;
        height: 1123px;
        border: 0;
        transform: scale(0.75);
        transform-origin: top left;
    }

    @media (max-width: 640px) {
        .cv-modal-thumb {
            width: 346px;
            height: 489px;
        }

        .cv-modal-thumb iframe {
            transform: scale(0.435);
        }
    }
</style>