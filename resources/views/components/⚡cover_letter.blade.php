<?php

use Livewire\Component;

new class extends Component
{
    public ?int $id = null;
    
    public string $title = '';

    public string $content = '';

    public array $letters = [];

    public function mount(): void
    {
        $this->letters = auth()->user()->coverLetters()->pluck('title', 'id')->map(function ($title, $id) {
            return ['id' => $id, 'title' => $title];
        })->toArray();
    }

    public function save($asNew = false): void
    {
        $this->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
        ]);

        if ($asNew) {
            $this->id = null;
        }

        $coverLetter = auth()->user()->coverLetters()->updateOrCreate(
            ['id' => $this->id ?? null],
            ['title' => $this->title, 'content' => $this->content]
        );

        $this->id = $coverLetter->id;

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
            $this->id = $coverLetter->id;
            $this->title = $coverLetter->title;
            $this->content = $coverLetter->content;
        }
        
        $this->dispatch('cover-letter-loaded', title: $this->title, content: $this->content);
    }

    public function clear(): void
    {
        $this->id = null;
        $this->title = '';
        $this->content = '';
        $this->dispatch('cover-letter-cleared');
    }

    public function delete(): void
    {
        if ($this->id) {
            auth()->user()->coverLetters()->where('id', $this->id)->delete();
            $this->id = null;
            $this->title = '';
            $this->content = '';
            $this->letters = auth()->user()->coverLetters()->pluck('title', 'id')->map(function ($title, $id) {
                return ['id' => $id, 'title' => $title];
            })->toArray();
            $this->dispatch('cover-letter-deleted');
            $this->dispatch('toast', message: __('Cover letter deleted.'), type: 'success');
        }
    }
};
?>

<div class="py-10 mx-4"
    x-data="{
        letter: @entangle('content'),
        saved: '',
        hasSaved: false,
        showDiff: true,
        dirty: false,
        _cache: null,
        get words() { const t = this.letter.trim(); return t ? t.split(/\s+/).length : 0 },
        get paragraphs() { const t = this.letter.trim(); return t ? t.split(/\n{2,}/).length : 0 },
        deleteLetter() {
            if (confirm('Are you sure you want to delete this cover letter?')) {
                $wire.delete();
            }
        },
        get changed() { return this.hasSaved && this.letter !== this.saved },
        get diffData() {
            if (!this.changed) return null;
            if (this._cache && this._cache.a === this.saved && this._cache.b === this.letter) return this._cache.out;
            this._cache = { a: this.saved, b: this.letter, out: this.buildDiff(this.saved, this.letter) };
            return this._cache.out;
        },
        get diff() { return this.showDiff ? this.diffData : null },
        escape(s) { return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') },
        buildDiff(a, b) {
            const split = (s) => s.split(/(\s+)/).filter((t) => t.length);
            let ta = split(a), tb = split(b);

            // Cut common prefix/suffix so the LCS table stays small.
            let p = 0;
            while (p < ta.length && p < tb.length && ta[p] === tb[p]) p++;
            let sfx = 0;
            while (sfx < ta.length - p && sfx < tb.length - p && ta[ta.length - 1 - sfx] === tb[tb.length - 1 - sfx]) sfx++;
            const head = ta.slice(0, p);
            const tail = ta.slice(ta.length - sfx);
            const ma = ta.slice(p, ta.length - sfx);
            const mb = tb.slice(p, tb.length - sfx);

            const n = ma.length, m = mb.length;
            const dp = Array.from({ length: n + 1 }, () => new Uint32Array(m + 1));
            for (let i = n - 1; i >= 0; i--) {
                for (let j = m - 1; j >= 0; j--) {
                    dp[i][j] = ma[i] === mb[j] ? dp[i + 1][j + 1] + 1 : Math.max(dp[i + 1][j], dp[i][j + 1]);
                }
            }
            const ops = [];
            const push = (type, text) => {
                const last = ops[ops.length - 1];
                if (last && last.type === type) last.text += text; else ops.push({ type, text });
            };
            let i = 0, j = 0;
            while (i < n && j < m) {
                if (ma[i] === mb[j]) { push('same', ma[i]); i++; j++; }
                else if (dp[i + 1][j] >= dp[i][j + 1]) { push('del', ma[i]); i++; }
                else { push('add', mb[j]); j++; }
            }
            while (i < n) { push('del', ma[i]); i++; }
            while (j < m) { push('add', mb[j]); j++; }

            let html = head.map((t) => this.escape(t)).join('');
            let added = 0, removed = 0;
            for (const op of ops) {
                if (op.type === 'same') { html += this.escape(op.text); continue; }
                if (op.text.trim()) added += op.type === 'add' ? op.text.trim().split(/\s+/).length : 0;
                if (op.text.trim()) removed += op.type === 'del' ? op.text.trim().split(/\s+/).length : 0;
                const cls = op.type === 'add'
                    ? 'bg-green-100 text-green-900 rounded-sm no-underline'
                    : 'bg-red-100 text-red-900 rounded-sm line-through';
                html += '<' + op.type + ' class=\'' + cls + '\'>' + this.escape(op.text) + '</' + op.type + '>';
            }
            html += tail.map((t) => this.escape(t)).join('');
            return { html, added, removed };
        },
    }"
    x-on:cover-letter-saved.window="saved = $wire.content; hasSaved = saved.trim() !== ''; dirty = false"
    x-on:cover-letter-loaded.window="letter = $event.detail.content; saved = $event.detail.content; hasSaved = saved.trim() !== ''; dirty = false; showDiff = true"
    x-on:cover-letter-cleared.window="letter = ''; saved = ''; hasSaved = false; dirty = false; showDiff = true">
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
                @if ($id)
                <button type="button" class="btn btn-error" @click="deleteLetter()">
                    Delete
                </button>
                @endif
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
                <button type="button" class="btn" wire:click="save(true)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    Save as New
                    <span wire:loading wire:target="save(true)" class="loading loading-spinner loading-xs"></span>
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
                        <div class="flex items-center gap-2">
                            <template x-if="diffData">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="font-medium text-green-600" x-cloak>+<span x-text="diffData.added">0</span></span>
                                    <span class="font-medium text-red-600" x-cloak>−<span x-text="diffData.removed">0</span></span>
                                    <button type="button" class="btn btn-ghost btn-xs"
                                        @click="showDiff = !showDiff"
                                        x-text="showDiff ? '{{ __('Hide changes') }}' : '{{ __('Show changes') }}'"></button>
                                </div>
                            </template>
                        </div>
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
                            class="whitespace-pre-wrap break-words text-[15px] leading-relaxed [&_ins]:no-underline"
                            x-html="diff ? diff.html : escape(letter)"></div>
                        <div x-show="!letter.trim()" class="italic text-neutral-400 text-[15px]">
                            {{ __('Your cover letter will appear here as you type...') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>