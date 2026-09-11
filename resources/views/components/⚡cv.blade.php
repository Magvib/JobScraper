<?php

use Livewire\Component;
use Illuminate\Support\Facades\URL;

new class extends Component
{
    public string $search = '';

    public ?string $selected = null;

    public array $templates = [];

    public string $signedRoute = '';

    public function mount()
    {
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
        $this->signedRoute = URL::temporarySignedRoute('signed-template', now()->addMinutes(100), ['slug' => $slug, 'user' => auth()->id()]);
        dd($this->signedRoute);
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
                <h1 class="text-3xl font-bold">CV Templates</h1>
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
                        <a class="btn btn-sm btn-ghost" href="{{ route('template', $template['slug']) }}"
                            target="_blank" title="Open full page in new tab">
                            Full page ↗
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if ($selected)
        <div class="modal modal-open" wire:keydown.escape.window="close">
            <div class="modal-box max-w-2xl p-4 flex flex-col items-center gap-3">
                <div class="cv-modal-thumb" wire:key="modal-frame-{{ $selected }}" wire:ignore>
                    <iframe src="{{ route('template', $selected) }}" title="Preview"></iframe>
                </div>
                <div class="modal-action w-full justify-center flex flex-col">
                    @php
                        $selectedTemplate = collect($this->templates)->firstWhere('slug', $selected);
                    @endphp
                    <span class="self-center card-title text-base leading-tight mb-2">
                        {{ $selectedTemplate['name'] ?? $selected }}
                    </span>
                    <div class="flex flex-row gap-2">
                        <button class="btn flex-1" wire:click="close">Close</button>
                        <a class="btn flex-1" href="{{ route('template', $selected) }}" target="_blank">
                            Open full page ↗
                        </a>
                    </div>
                </div>
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