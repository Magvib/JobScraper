<?php

use Livewire\Component;

new class extends Component
{
    public string $title = '';

    public string $content = '';

    /**
     * Saved cover letters shown in the "Load" dropdown.
     * TODO: populate in your load logic, expected shape:
     * [['id' => 1, 'title' => 'Frontend developer'], ...]
     */
    public array $letters = [];

    public function mount(): void
    {
        $this->letters = auth()->user()->coverLetters()->pluck('title', 'id')->map(function ($title, $id) {
            return ['id' => $id, 'title' => $title];
        })->toArray();
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);
        auth()->user()->coverLetters()->updateOrCreate(
            ['id' => $this->letters['id'] ?? null],
            ['title' => $this->title, 'content' => $this->content]
        );

        // Refresh the list of saved letters after saving.
        $this->letters = auth()->user()->coverLetters()->pluck('title', 'id')->map(function ($title, $id) {
            return ['id' => $id, 'title' => $title];
        })->toArray();
        
        $this->dispatch('cover-letter-saved');
        $this->dispatch('toast', message: __('Cover letter saved.'), type: 'success');
    }

    public function load(string $id): void
    {
        $coverLetter = auth()->user()->coverLetters()->find($id);

        if ($coverLetter) {
            $this->title = $coverLetter->title;
            $this->content = $coverLetter->content;
        }
        
        $this->dispatch('cover-letter-loaded', title: $this->title, content: $this->content);
    }

    public function clear(): void
    {
        $this->title = '';
        $this->content = '';
        $this->dispatch('cover-letter-cleared');
    }
};
?>

<div class="py-10 mx-4"
    x-data="{
        letter: @entangle('content'),
        dirty: false,
        get words() { const t = this.letter.trim(); return t ? t.split(/\s+/).length : 0 },
        get paragraphs() { const t = this.letter.trim(); return t ? t.split(/\n{2,}/).length : 0 },
    }"
    x-on:cover-letter-saved.window="dirty = false"
    x-on:cover-letter-loaded.window="dirty = false"
    x-on:cover-letter-cleared.window="dirty = false">
    <div class="max-w-8xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold">Cover Letter Editor</h1>
                <p class="mt-2 opacity-70">
                    <span x-show="!dirty" x-cloak>All changes saved</span>
                    <span x-show="dirty" class="text-warning flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-warning inline-block"></span>
                        Unsaved changes
                    </span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                {{-- Load saved letters --}}
                <details class="dropdown dropdown-end">
                    <summary class="btn btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Load
                    </summary>
                    <ul class="dropdown-content menu bg-base-100 rounded-box shadow-lg border border-base-300 w-64 z-30 mt-1">
                        @if (count($letters) === 0)
                            <li class="disabled"><span class="opacity-60">No saved letters yet</span></li>
                        @else
                            @foreach ($letters as $letter)
                                <li wire:key="saved-{{ $letter['id'] }}">
                                    <button wire:click="load('{{ $letter['id'] }}')">
                                        {{ $letter['title'] ?? __('Untitled') }}
                                    </button>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </details>

                <button type="button" class="btn btn-ghost text-error" @click="$wire.clear()">
                    Clear
                </button>
                <button type="button" class="btn btn-primary" wire:click="save">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    Save
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            {{-- Editor --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <label class="form-control">
                        <span class="label-text mb-1 block text-sm opacity-70">{{ __('Title') }}</span>
                        <input type="text" wire:model="title" x-on:input="dirty = true"
                            class="input input-bordered w-full"
                            placeholder="{{ __('e.g. Frontend developer — Novo Nordisk') }}" />
                    </label>

                    <div class="relative">
                        <textarea
                            x-ref="editor"
                            x-model="letter"
                            x-on:input="dirty = true"
                            x-on:keydown.meta.s.prevent="$wire.save()"
                            x-on:keydown.ctrl.s.prevent="$wire.save()"
                            class="textarea textarea-bordered w-full min-h-152 leading-relaxed font-serif text-base"
                            placeholder="{{ __('Dear Hiring Manager,') }}&#10;&#10;{{ __('Start writing your cover letter here...') }}"></textarea>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm opacity-70 border-t border-base-300 pt-3">
                        <span><span x-text="words" class="font-semibold">0</span> {{ __('words') }}</span>
                        <span><span x-text="letter.length" class="font-semibold">0</span> {{ __('characters') }}</span>
                        <span><span x-text="paragraphs" class="font-semibold">0</span> {{ __('paragraphs') }}</span>
                        <span class="ml-auto">~<span x-text="Math.max(1, Math.ceil(words / 200))">1</span> {{ __('min read') }}</span>
                    </div>
                </div>
            </div>

            {{-- Live preview --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="card-title text-base">{{ __('Preview') }}</h2>
                        <!-- <span class="badge badge-ghost badge-sm">{{ __('as printed') }}</span> -->
                    </div>

                    <div class="bg-white text-neutral-800 rounded-lg shadow-inner border border-base-300 p-8 font-serif min-h-152 max-h-192 overflow-y-auto">
                        <div class="flex justify-between items-start mb-8 gap-4 border-b border-neutral-200 pb-4">
                            <div>
                                <p class="font-bold text-base">@auth {{ auth()->user()->name }} @endauth</p>
                                <p class="text-xs text-neutral-500">
                                    @auth
                                        {{ auth()->user()->address }}
                                        @if (auth()->user()->address && (auth()->user()->zip || auth()->user()->city)), @endif
                                        {{ trim(auth()->user()->zip . ' ' . auth()->user()->city) }}
                                    @endauth
                                </p>
                            </div>
                            <div class="text-xs text-neutral-500 text-right">
                                <p>@auth {{ auth()->user()->email }} @endauth</p>
                                <p>@auth {{ auth()->user()->phone }} @endauth</p>
                            </div>
                        </div>

                        <div x-show="letter.trim()" x-cloak
                            class="whitespace-pre-wrap break-words text-[15px] leading-relaxed" x-text="letter"></div>
                        <div x-show="!letter.trim()" class="italic text-neutral-400 text-[15px]">
                            {{ __('Your cover letter will appear here as you type...') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>