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

<div>
    <x-page-header
        :title="__('Templates')"
        :description="__('Showing :shown of :total templates — click one to preview', ['shown' => count($this->filteredTemplates), 'total' => count($templates)])"
    >
        <div class="relative w-full sm:w-72">
            <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
            <x-ui.input type="search" class="pl-9" placeholder="{{ __('Search templates...') }}" wire:model.live.debounce.300ms="search" />
        </div>
    </x-page-header>

    @if (count($this->filteredTemplates) === 0)
        <x-ui.empty icon="search" :title="__('No templates match “:search”', ['search' => $search])" :description="__('Try another search.')" />
    @endif

    <div class="grid justify-center gap-6" style="grid-template-columns: repeat(auto-fill, 264px)">
        @foreach ($this->filteredTemplates as $template)
            <x-ui.card size="flush" wire:key="tpl-{{ $template['slug'] }}" class="group/tpl overflow-hidden transition-shadow hover:shadow-md">
                <button type="button" class="block cursor-pointer border-b text-left" wire:click="select('{{ $template['slug'] }}')" aria-label="{{ __('Preview :name', ['name' => $template['name']]) }}">
                    <div class="cv-thumb" wire:ignore>
                        <iframe src="{{ route('template', $template['slug']) }}" loading="lazy" scrolling="no" title="{{ $template['name'] }}" tabindex="-1"></iframe>
                    </div>
                </button>
                <div class="flex items-center justify-between gap-2 px-4 py-3">
                    <div class="min-w-0">
                        <h2 class="truncate text-sm font-medium leading-tight">{{ $template['name'] }}</h2>
                        <p class="text-xs text-muted-foreground">#{{ $template['number'] }}</p>
                    </div>
                    <x-ui.button variant="ghost" size="sm" wire:click="download('{{ $template['slug'] }}')" wire:loading.attr="disabled" wire:target="download('{{ $template['slug'] }}')" title="{{ __('Download PDF') }}">
                        <x-ui.spinner wire:loading wire:target="download('{{ $template['slug'] }}')" />
                        <x-ui.icon name="download" wire:loading.remove wire:target="download('{{ $template['slug'] }}')" />
                        {{ __('PDF') }}
                    </x-ui.button>
                </div>
            </x-ui.card>
        @endforeach
    </div>

    @if ($selected)
        @php
            $firstLetterId = collect($letters)->first()['id'] ?? null;
            $selectedTemplate = collect($this->templates)->firstWhere('slug', $selected);
        @endphp

        <x-ui.dialog close="$wire.close()" max-width="max-w-7xl" padding="p-4">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-semibold leading-tight">{{ $selectedTemplate['name'] ?? $selected }}</h3>
                <x-ui.button variant="ghost" size="icon-sm" wire:click="close" aria-label="{{ __('Close') }}">
                    <x-ui.icon name="x" />
                </x-ui.button>
            </div>

            <div class="flex min-h-0 flex-col items-center gap-3 overflow-y-auto"
                x-data="{ letterId: {{ $firstLetterId ?? 'null' }}, letters: @entangle('letters') }">
                <div class="relative flex flex-col items-start gap-3 lg:flex-row">
                    <div class="cv-modal-thumb" wire:key="modal-frame-{{ $selected }}" wire:ignore>
                        <iframe src="{{ route('template', $selected) }}" title="{{ __('Preview') }}"></iframe>
                    </div>

                    <div class="relative">
                        {{-- Switch between saved cover letters, floating over the letter preview --}}
                        <div class="absolute right-3 top-3 z-30">
                            <x-ui.dropdown width="w-64">
                                <x-slot:trigger>
                                    <x-ui.button variant="outline" size="sm" class="bg-background/90 backdrop-blur-sm">
                                        <x-ui.icon name="file-text" />
                                        <span class="max-w-40 truncate" x-text="letters.find(l => l.id == letterId)?.title ?? '{{ __('No cover letter') }}'">{{ __('Cover letter') }}</span>
                                        <x-ui.icon name="chevron-down" size="size-3.5" class="opacity-60" />
                                    </x-ui.button>
                                </x-slot:trigger>

                                @forelse ($letters as $letter)
                                    <x-ui.dropdown-item wire:key="modal-letter-{{ $letter['id'] }}" x-on:click="letterId = {{ $letter['id'] }}" x-bind:class="letterId == {{ $letter['id'] }} && 'bg-accent font-medium'">
                                        <span class="truncate">{{ $letter['title'] ?? __('Untitled') }}</span>
                                    </x-ui.dropdown-item>
                                @empty
                                    <p class="px-2 py-1.5 text-sm text-muted-foreground">{{ __('No saved letters yet') }}</p>
                                @endforelse
                            </x-ui.dropdown>
                        </div>

                        <div class="cv-modal-thumb" wire:key="modal-letter-frame-{{ $selected }}" wire:ignore>
                            <iframe :src="'{{ route('template', ['name' => $selected]) }}' + (letterId ? '?coverLetter=' + letterId : '')" title="{{ __('Preview') }}"></iframe>
                        </div>
                    </div>

                    {{-- Download CV / Download Letter, anchored to the bottom of the previews --}}
                    <div class="absolute bottom-3 left-1/2 z-30 -translate-x-1/2">
                        <x-ui.dropdown side="top" align="start" width="w-56">
                            <x-slot:trigger>
                                <x-ui.button size="sm" class="shadow-md">
                                    <x-ui.icon name="download" /> {{ __('Download') }}
                                    <x-ui.icon name="chevron-up" size="size-3.5" class="opacity-70" />
                                </x-ui.button>
                            </x-slot:trigger>

                            <x-ui.dropdown-item wire:click="download('{{ $selected }}')">
                                <x-ui.icon name="file-text" /> {{ __('Download CV') }}
                            </x-ui.dropdown-item>
                            <x-ui.dropdown-item wire:click="download('{{ $selected }}', letterId)">
                                <x-ui.icon name="file-text" /> {{ __('Download Letter') }}
                            </x-ui.dropdown-item>
                        </x-ui.dropdown>
                    </div>
                </div>
            </div>

            <div wire:loading wire:target="download" class="absolute inset-x-0 bottom-4 z-40 flex justify-center">
                <span class="flex items-center gap-2 rounded-full border bg-popover px-4 py-2 text-sm text-popover-foreground shadow-lg">
                    <x-ui.spinner /> {{ __('Generating PDF…') }}
                </span>
            </div>
        </x-ui.dialog>
    @endif
</div>
