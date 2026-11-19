<?php

use App\Ai\Agents\CoverLetterSpecialist;
use App\Models\Post;
use Livewire\Component;

new class extends Component
{
    public ?int $id = null;

    public ?int $jobId = null;
    
    public string $title = '';

    public string $jobLink = '';

    public string $content = '';

    public array $letters = [];

    public function mount(): void
    {
        $this->letters = auth()->user()->coverLetters()->pluck('title', 'id')->map(function ($title, $id) {
            return ['id' => $id, 'title' => $title];
        })->toArray();
    }

    public function with(): array
    {
        $data = [];
        
        if (strlen($this->jobLink) > 3) {
            $data['jobs'] = Post::where('title', 'like', '%' . $this->jobLink . '%')->orWhere('company_name', 'like', '%' . $this->jobLink . '%')->limit(5)->get();
        }

        if ($this->jobId) {
            $data['linkedJob'] = Post::find($this->jobId);
        }
        
        return $data;
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
            ['title' => $this->title, 'content' => $this->content, 'job_id' => $this->jobId]
        );

        $this->id = $coverLetter->id;

        // Refresh the list of saved letters after saving.
        $this->letters = auth()->user()->coverLetters()->pluck('title', 'id')->map(function ($title, $id) {
            return ['id' => $id, 'title' => $title];
        })->toArray();
        
        $this->jobLink = '';
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
            $this->jobId = $coverLetter->job_id;
        }
        
        $this->dispatch('cover-letter-loaded', title: $this->title, content: $this->content);
    }

    public function clear(): void
    {
        $this->id = null;
        $this->title = '';
        $this->content = '';
        $this->jobId = null;
        $this->dispatch('cover-letter-cleared');
    }

    public function delete(): void
    {
        if ($this->id) {
            auth()->user()->coverLetters()->where('id', $this->id)->delete();
            $this->id = null;
            $this->title = '';
            $this->content = '';
            $this->jobId = null;
            $this->letters = auth()->user()->coverLetters()->pluck('title', 'id')->map(function ($title, $id) {
                return ['id' => $id, 'title' => $title];
            })->toArray();
            $this->dispatch('cover-letter-deleted');
            $this->dispatch('toast', message: __('Cover letter deleted.'), type: 'success');
        }
    }

    public function aiPrompt($prompt, $letter = "", $selectedText = "")
    {
        $now = now();
        $jobDescription = "";

        if ($this->jobId) {
            $job = Post::find($this->jobId);
            if ($job) {
                $jobDescription = $job->fetchDescription() ?? '';
            }
        }
        
        $response = (new CoverLetterSpecialist)->prompt(<<<PROMPT
            Current date: $now

            ---Prompt---
            $prompt
            ---End Prompt---

            ---Selected Text---
            $selectedText
            ---End Selected Text---

            ---Cover Letter---
            $letter
            ---End Cover Letter---

            ---Job Description---
            $jobDescription
            ---End Job Description---
            PROMPT
        );

        $this->dispatch('cover-letter-change', content: $response->text);
        $this->dispatch('toast', message: __('Cover letter updated.'), type: 'success');
    }

    public function getDescription($jobId, $force = false)
    {
        $job = Post::find($jobId);

        if ($job?->fetchDescription($force)) {
            $this->dispatch('toast', message: __('Job description fetched.'), type: 'success');
        } else {
            $this->dispatch('toast', message: __('Could not fetch the job description.'), type: 'error');
        }
    }
};
?>

<div
    x-data="{
        letter: @entangle('content'),
        saved: '',
        savedTitle: '',
        hasSaved: false,
        showDiff: true,
        _cache: null,
        get words() { const t = this.letter.trim(); return t ? t.split(/\s+/).length : 0 },
        get paragraphs() { const t = this.letter.trim(); return t ? t.split(/\n{2,}/).length : 0 },
        deleteLetter() {
            if (confirm('Are you sure you want to delete this cover letter?')) {
                $wire.delete();
            }
        },
        // Popup shown above the text selected in the preview.
        popup: { open: false, x: 0, y: 0, h: 0, above: true, text: '', prompt: '' },
        _hl: null,
        // Unwrap the frozen yellow highlight span, if any.
        clearHighlight() {
            if (this._hl) {
                this._hl.replaceWith(...this._hl.childNodes);
                this._hl = null;
            }
        },
        selectionChanged() {
            this.clearHighlight();
            const sel = window.getSelection();
            if (!sel || sel.isCollapsed || sel.rangeCount === 0) return;
            const range = sel.getRangeAt(0);
            const box = this.$refs.preview;
            if (!box || !box.contains(range.commonAncestorContainer)) return;
            const text = sel.toString().trim();
            if (text.length < 2) { this.popup.open = false; return; }
            const r = range.getBoundingClientRect();
            const c = box.getBoundingClientRect();
            this.popup.text = text;
            // Center above the selection, clamped so the popup stays inside the preview.
            this.popup.x = Math.min(Math.max(r.left + r.width / 2 - c.left, 168), c.width - 168);
            this.popup.y = r.top - c.top;
            this.popup.h = r.height;
            // Flip below the selection when there is not enough room above it.
            this.popup.above = r.top - c.top > 130;
            this.popup.open = true;
            this.$nextTick(() => this.$refs.popupInput?.focus());
            // Freeze the selection as a yellow span so it stays visible while the
            // popup is used (the browser clears the native selection on click).
            const span = document.createElement('span');
            span.className = 'ai-selection-highlight';
            const frag = range.extractContents();
            span.appendChild(frag);
            range.insertNode(span);
            this._hl = span;
            window.getSelection()?.removeAllRanges();
        },
        runPrompt(prompt) {
            if (!prompt.trim() || !this.popup.text) return;
            console.log('[AI] prompt:', prompt, '→ selection:', this.popup.text);
            this.popup.open = false;
            this.popup.prompt = '';
            // The yellow highlight is kept so the selection stays visible.
            $wire.aiPrompt(prompt, this.letter, this.popup.text);
        },
        // Prompt panel that works on the whole letter, no selection needed.
        whole: { open: false, prompt: '' },
        toggleWhole() {
            this.whole.prompt = '';
            this.whole.open = !this.whole.open;
            if (this.whole.open) {
                this.popup.open = false;
                this.clearHighlight();
                // Defer until Alpine has applied x-show, else the input is
                // still display:none and focus() is a silent no-op.
                this.$nextTick(() => this.$refs.wholePrompt?.focus());
            }
        },
        runWholePrompt(prompt) {
            if (!prompt.trim() || !this.letter.trim()) return;
            console.log('[AI] prompt:', prompt, '→ whole letter');
            this.whole.open = false;
            this.whole.prompt = '';
            $wire.aiPrompt(prompt, this.letter);
        },
        // Real dirty check: current content/title vs. what is saved in the DB.
        get changed() {
            const savedContent = this.hasSaved ? this.saved : '';
            const savedTitle = this.hasSaved ? this.savedTitle : '';
            return this.letter !== savedContent || ($wire.title || '') !== savedTitle;
        },
        get contentChanged() { return this.hasSaved && this.letter !== this.saved },
        get diffData() {
            if (!this.contentChanged) return null;
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
    x-on:cover-letter-saved.window="saved = $wire.content; savedTitle = $wire.title; hasSaved = true"
    x-on:cover-letter-loaded.window="letter = $event.detail.content; saved = $event.detail.content; savedTitle = $wire.title; hasSaved = true; showDiff = true"
    x-on:cover-letter-cleared.window="letter = ''; saved = ''; savedTitle = ''; hasSaved = false; showDiff = true"
    x-on:cover-letter-deleted.window="letter = ''; saved = ''; savedTitle = ''; hasSaved = false; showDiff = true"
    x-on:cover-letter-change.window="letter = $event.detail.content; showDiff = true">
    @php($user = auth()->user())

    <x-page-header :title="__('Cover Letter Editor')">
        <x-slot:subtitle>
            <span x-show="changed" class="flex items-center gap-2 text-warning">
                <span class="size-2 rounded-full bg-warning"></span>
                {{ __('Unsaved changes') }}
            </span>
            <span x-show="!changed" x-cloak>{{ __('All changes saved') }}</span>
        </x-slot:subtitle>

        @if ($id)
            <x-ui.button variant="destructive" x-on:click="deleteLetter()">
                <x-ui.icon name="trash" /> {{ __('Delete') }}
            </x-ui.button>
        @endif

        {{-- Load saved letters --}}
        <x-ui.dropdown width="w-80">
            <x-slot:trigger>
                <x-ui.button variant="outline">
                    <x-ui.icon name="download" /> {{ __('Load') }}
                    <x-ui.icon name="chevron-down" size="size-3.5" class="opacity-60" />
                </x-ui.button>
            </x-slot:trigger>

            @forelse ($letters as $letter)
                <x-ui.dropdown-item wire:key="saved-{{ $letter['id'] }}" wire:click="load('{{ $letter['id'] }}')" :active="$letter['id'] === $id">
                    <span class="truncate">{{ $letter['title'] ?? __('Untitled') }}</span>
                </x-ui.dropdown-item>
            @empty
                <p class="px-2 py-1.5 text-sm text-muted-foreground">{{ __('No saved letters yet') }}</p>
            @endforelse
        </x-ui.dropdown>

        <x-ui.button variant="ghost" class="text-destructive hover:text-destructive" x-on:click="$wire.clear()">
            {{ __('Clear') }}
        </x-ui.button>

        <x-ui.button variant="outline" wire:click="save(true)" wire:loading.attr="disabled" wire:target="save(true)">
            <x-ui.spinner wire:loading wire:target="save(true)" />
            {{ __('Save as New') }}
        </x-ui.button>

        <x-ui.button wire:click="save" wire:loading.attr="disabled" wire:target="save">
            <x-ui.spinner wire:loading wire:target="save" />
            <x-ui.icon name="save" wire:loading.remove wire:target="save" />
            {{ __('Save') }}
        </x-ui.button>
    </x-page-header>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-2">
        {{-- Editor --}}
        <x-ui.card>
            <x-ui.card-content class="flex flex-col gap-4">
                <x-ui.field :label="__('Title')" for="title" error="title">
                    <x-ui.input id="title" wire:model="title" placeholder="{{ __('e.g. Frontend developer — Novo Nordisk') }}" />
                </x-ui.field>

                <div class="relative">
                    <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                    <x-ui.input type="search" class="pl-9" placeholder="{{ __('Link it to a job') }}" wire:model.live.debounce.300ms="jobLink" />
                </div>

                {{-- Currently linked job, shown even when the search list doesn't include it --}}
                @if (isset($linkedJob) && ! collect($jobs ?? [])->contains('id', $linkedJob->id))
                    <div x-data="{ showDescription: false }" class="space-y-2 rounded-lg border p-3">
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <x-ui.badge>{{ __('Linked') }}</x-ui.badge>
                            <span class="min-w-0 truncate">{{ $linkedJob->title }} at {{ $linkedJob->company_name }}</span>
                            <div class="ml-auto flex items-center gap-1">
                                @if ($linkedJob->description)
                                    <x-ui.button variant="outline" size="xs" wire:click="getDescription({{ $linkedJob->id }}, true)">{{ __('Reload Description') }}</x-ui.button>
                                    <x-ui.button variant="ghost" size="xs" x-on:click="showDescription = !showDescription">
                                        {{ __('Description') }}
                                        <x-ui.icon name="chevron-down" size="size-3" class="transition-transform" x-bind:class="showDescription && 'rotate-180'" />
                                    </x-ui.button>
                                @else
                                    <x-ui.button variant="ghost" size="xs" wire:click="getDescription({{ $linkedJob->id }})">{{ __('Get Description') }}</x-ui.button>
                                @endif
                                <x-ui.button variant="ghost" size="xs" wire:click="$set('jobId', null)">{{ __('Unlink') }}</x-ui.button>
                            </div>
                        </div>
                        @if ($linkedJob->description)
                            <div x-show="showDescription" x-cloak x-transition.opacity class="job-desc max-h-72 overflow-y-auto rounded-lg border bg-muted/40 p-3 text-sm">
                                {!! $linkedJob->safeDescription() !!}
                            </div>
                        @endif
                    </div>
                @endif

                @if (! empty($jobs) && count($jobs))
                    <div class="space-y-1.5">
                        @foreach ($jobs as $job)
                            <button
                                type="button"
                                wire:key="job-link-{{ $job->id }}"
                                @class([
                                    'w-full rounded-lg border px-3 py-2 text-left text-sm transition-colors',
                                    'border-primary bg-accent font-medium' => $job->id === $jobId,
                                    'hover:bg-accent/60' => $job->id !== $jobId,
                                ])
                                wire:click="$set('jobId', {{ $job->id === $jobId ? 'null' : $job->id }})"
                            >
                                <span class="flex items-center gap-2">
                                    <span class="truncate">{{ $job->title }} at {{ $job->company_name }}</span>
                                    @if ($job->id === $jobId)
                                        <x-ui.icon name="check" class="ml-auto" />
                                    @endif
                                </span>
                            </button>

                            @if ($job->id === $jobId && $job->description)
                                <div x-data="{ showDescription: false }" wire:key="job-desc-{{ $job->id }}">
                                    <x-ui.button variant="ghost" size="xs" x-on:click="showDescription = !showDescription">
                                        {{ __('Description') }}
                                        <x-ui.icon name="chevron-down" size="size-3" class="transition-transform" x-bind:class="showDescription && 'rotate-180'" />
                                    </x-ui.button>
                                    <div x-show="showDescription" x-cloak x-transition.opacity class="job-desc mt-1 max-h-72 overflow-y-auto rounded-lg border bg-muted/40 p-3 text-sm">
                                        {!! $job->safeDescription() !!}
                                    </div>
                                </div>
                            @elseif ($job->id === $jobId)
                                <x-ui.button variant="ghost" size="xs" wire:key="job-desc-{{ $job->id }}" wire:click="getDescription({{ $job->id }})">
                                    {{ __('Get Description') }}
                                    <x-ui.spinner size="size-3" wire:loading wire:target="getDescription({{ $job->id }})" />
                                </x-ui.button>
                            @endif
                        @endforeach
                    </div>
                @endif

                <x-ui.field error="content">
                    <x-ui.textarea
                        x-ref="editor"
                        x-model="letter"
                        x-on:keydown.meta.s.prevent="$wire.save()"
                        x-on:keydown.ctrl.s.prevent="$wire.save()"
                        class="min-h-[38rem] font-serif text-base leading-relaxed"
                        placeholder="{{ __('Dear Hiring Manager,') }}&#10;&#10;{{ __('Start writing your cover letter here...') }}"
                    />
                </x-ui.field>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 border-t pt-3 text-sm text-muted-foreground">
                    <span><span x-text="words" class="font-semibold text-foreground">0</span> {{ __('words') }}</span>
                    <span><span x-text="letter.length" class="font-semibold text-foreground">0</span> {{ __('characters') }}</span>
                    <span><span x-text="paragraphs" class="font-semibold text-foreground">0</span> {{ __('paragraphs') }}</span>
                    <span class="ml-auto">~<span x-text="Math.max(1, Math.ceil(words / 200))">1</span> {{ __('min read') }}</span>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Live preview --}}
        <x-ui.card class="lg:sticky lg:top-20">
            <x-ui.card-header class="flex-row items-center justify-between">
                <x-ui.card-title>{{ __('Preview') }}</x-ui.card-title>
                <div class="flex items-center gap-2">
                    <x-ui.button
                        variant="outline"
                        size="sm"
                        x-bind:class="whole.open && 'ring-2 ring-ring'"
                        x-on:click="toggleWhole()"
                        title="{{ __('AI assistant for the whole letter') }}"
                    >
                        <x-ui.icon name="sparkles" /> {{ __('AI') }}
                    </x-ui.button>
                    <template x-if="diffData">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="font-medium text-success">+<span x-text="diffData.added">0</span></span>
                            <span class="font-medium text-destructive">−<span x-text="diffData.removed">0</span></span>
                            <x-ui.button variant="ghost" size="xs" x-on:click="showDiff = !showDiff" x-text="showDiff ? '{{ __('Hide changes') }}' : '{{ __('Show changes') }}'" />
                        </div>
                    </template>
                </div>
            </x-ui.card-header>

            <x-ui.card-content>
                {{-- The paper stays white in dark mode: it previews a printed document. --}}
                <div x-ref="preview" class="relative max-h-[297mm] min-h-[38rem] overflow-y-auto rounded-lg border bg-white p-8 font-serif text-neutral-800 shadow-inner"
                    @mouseup="!$event.target.closest('#selection-popup, #whole-letter-popup') && selectionChanged()"
                    @keyup="!$event.target.closest('#selection-popup, #whole-letter-popup') && selectionChanged()"
                    @mousedown.window="!$event.target.closest('#selection-popup, #whole-letter-popup') && (clearHighlight(), popup.open = false)"
                    @keydown.escape.window="clearHighlight(), popup.open = false, whole.open = false">
                    <div class="mb-8 flex items-start justify-between gap-4 border-b border-neutral-200 pb-4">
                        <div>
                            <p class="text-base font-bold">{{ $user->name }}</p>
                            <p class="text-xs text-neutral-500">
                                {{ $user->address }}@if ($user->address && ($user->zip || $user->city)), @endif
                                {{ trim($user->zip.' '.$user->city) }}
                            </p>
                        </div>
                        <div class="text-right text-xs text-neutral-500">
                            <p>{{ $user->email }}</p>
                            <p>{{ $user->phone }}</p>
                        </div>
                    </div>

                    <div x-show="letter.trim()" x-cloak
                        class="whitespace-pre-wrap break-words text-[15px] leading-relaxed [&_ins]:no-underline"
                        x-html="diff ? diff.html : escape(letter)"></div>
                    <div x-show="!letter.trim()" class="text-[15px] italic text-neutral-400">
                        {{ __('Your cover letter will appear here as you type...') }}
                    </div>

                    {{-- Prompt panel for the whole letter, no selection needed --}}
                    <div id="whole-letter-popup" x-show="whole.open" x-cloak
                        x-transition.opacity.duration.100ms
                        class="absolute left-1/2 top-4 z-40 w-96 max-w-[calc(100%-2rem)] -translate-x-1/2 rounded-lg border bg-popover p-3 font-sans text-popover-foreground shadow-xl">
                        <p class="mb-2 text-xs font-medium text-muted-foreground">{{ __('Improve the whole letter') }}</p>
                        <div class="flex gap-1.5">
                            <x-ui.input x-ref="wholePrompt" x-model="whole.prompt"
                                x-on:keydown.enter.prevent="runWholePrompt(whole.prompt)"
                                class="h-8 text-sm"
                                placeholder="{{ __('e.g. Make the whole letter shorter') }}" />
                            <x-ui.button size="icon-sm" x-bind:disabled="!letter.trim()" x-on:click="runWholePrompt(whole.prompt)" aria-label="{{ __('Send') }}">
                                <x-ui.icon name="arrow-right" />
                            </x-ui.button>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <x-ui.button variant="outline" size="xs" x-bind:disabled="!letter.trim()" x-on:click="runWholePrompt('Improve the whole letter')">{{ __('Improve letter') }}</x-ui.button>
                            <x-ui.button variant="outline" size="xs" x-bind:disabled="!letter.trim()" x-on:click="runWholePrompt('Make the whole letter more concise')">{{ __('Make it shorter') }}</x-ui.button>
                            <x-ui.button variant="outline" size="xs" x-bind:disabled="!letter.trim()" x-on:click="runWholePrompt('Tailor the letter to the job description')">{{ __('Tailor to job') }}</x-ui.button>
                        </div>
                        <p x-show="!letter.trim()" class="mt-2 text-xs text-warning">
                            {{ __('Write something first — the AI works on the whole letter.') }}
                        </p>
                    </div>

                    {{-- Loading overlay shown while the AI prompt is running --}}
                    <div wire:loading wire:target="aiPrompt" x-cloak>
                        <div class="absolute inset-0 z-50 flex flex-col items-center justify-center gap-3 rounded-lg bg-white/80 font-sans">
                            <x-ui.spinner size="size-6" class="text-neutral-600" />
                            <p class="text-sm italic text-neutral-500">{{ __('The AI is working on your letter...') }}</p>
                        </div>
                    </div>

                    {{-- Popup shown above the selected text --}}
                    <div id="selection-popup" x-show="popup.open" x-cloak
                        x-transition.opacity.duration.100ms
                        class="absolute z-40 w-80 rounded-lg border bg-popover p-3 font-sans text-popover-foreground shadow-xl"
                        :style="popup.above
                            ? `left:${popup.x}px; top:${popup.y}px; transform:translate(-50%, calc(-100% - 8px))`
                            : `left:${popup.x}px; top:${popup.y + popup.h + 8}px; transform:translate(-50%, 0)`">
                        <p class="mb-2 text-xs font-medium text-muted-foreground">{{ __('Rewrite selection') }}</p>
                        <div class="flex gap-1.5">
                            <x-ui.input x-model="popup.prompt" x-ref="popupInput"
                                x-on:keydown.enter.prevent="runPrompt(popup.prompt)"
                                class="h-8 text-sm"
                                placeholder="{{ __('e.g. Make this more professional') }}" />
                            <x-ui.button size="icon-sm" x-on:click="runPrompt(popup.prompt)" aria-label="{{ __('Send') }}">
                                <x-ui.icon name="arrow-right" />
                            </x-ui.button>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <x-ui.button variant="outline" size="xs" x-on:click="runPrompt('Make this more professional')">{{ __('More professional') }}</x-ui.button>
                            <x-ui.button variant="outline" size="xs" x-on:click="runPrompt('Make this more concise')">{{ __('More concise') }}</x-ui.button>
                            <x-ui.button variant="outline" size="xs" x-on:click="runPrompt('Fix grammar and spelling')">{{ __('Fix grammar') }}</x-ui.button>
                        </div>
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</div>
