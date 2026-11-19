<?php

use Livewire\Component;

new class extends Component
{
    public array $jobs = [];

    public function mount(): void
    {
        $this->jobs = auth()->user()->cv_json ?? [];
    }

    public function save(): void
    {
        $user = auth()->user();
        $user->cv_json = $this->jobs;
        $user->save();

        $this->dispatch('cv-saved');
        $this->dispatch('toast', message: __('CV saved.'), type: 'success');
    }
};
?>

<div
    x-data="{
        jobs: @entangle('jobs'),
        saved: '',
        hasLoaded: false,
        showDiff: true,
        _cache: null,
        init() {
            this.saved = JSON.stringify(this.jobs);
            this.hasLoaded = true;
        },
        get changed() {
            return this.hasLoaded && JSON.stringify(this.jobs) !== this.saved;
        },
        get diffData() {
            if (!this.changed) return null;
            const cur = JSON.stringify(this.jobs);
            if (this._cache && this._cache.a === this.saved && this._cache.b === cur) return this._cache.out;
            this._cache = { a: this.saved, b: cur, out: this.buildCvDiff() };
            return this._cache.out;
        },
        get diff() { return this.showDiff ? this.diffData : null },
        escape(s) { return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') },
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
        buildCvDiff() {
            const oldJobs = JSON.parse(this.saved || '[]');
            const curJobs = this.jobs;
            const key = (job) => JSON.stringify(job);

            // Entry-level LCS on whole entries so inserting or reordering
            // entries doesn't misalign the diff — same idea as the word-level
            // LCS in buildDiff, one level up.
            const n = oldJobs.length, m = curJobs.length;
            const dp = Array.from({ length: n + 1 }, () => new Uint32Array(m + 1));
            for (let i = n - 1; i >= 0; i--) {
                for (let j = m - 1; j >= 0; j--) {
                    dp[i][j] = key(oldJobs[i]) === key(curJobs[j]) ? dp[i + 1][j + 1] + 1 : Math.max(dp[i + 1][j], dp[i][j + 1]);
                }
            }
            const ops = [];
            const push = (type, item) => {
                const last = ops[ops.length - 1];
                if (last && last.type === type) last.items.push(item);
                else ops.push({ type, items: [item] });
            };
            let i = 0, j = 0;
            while (i < n && j < m) {
                if (key(oldJobs[i]) === key(curJobs[j])) { push('same', j); i++; j++; }
                else if (dp[i + 1][j] >= dp[i][j + 1]) { push('del', i); i++; }
                else { push('add', j); j++; }
            }
            while (i < n) { push('del', i); i++; }
            while (j < m) { push('add', j); j++; }

            // A removed run next to an added run is the same entry after an
            // edit: word-diff the paired entries field by field, treat the
            // leftovers as fully removed / fully added.
            const countWords = (s) => s.trim() ? s.trim().split(/\s+/).length : 0;
            const fieldValues = (job) => {
                job = job || {};
                return {
                    title: job.title || '',
                    company: job.company || '',
                    start: job.startDate ? this.fmt(job.startDate) : '',
                    end: job.endDate ? this.fmt(job.endDate) : '{{ __('nu') }}',
                    description: job.description || '',
                };
            };
            let addedWords = 0, removedWords = 0;
            const diffs = {};
            const diffFields = (index, oldJob, newJob) => {
                const oa = fieldValues(oldJob), nb = fieldValues(newJob);
                diffs[index] = {};
                for (const f of ['title', 'company', 'start', 'end', 'description']) {
                    if (oa[f] === nb[f]) continue;
                    const out = this.buildDiff(oa[f], nb[f]);
                    diffs[index][f] = out.html;
                    addedWords += out.added;
                    removedWords += out.removed;
                }
            };
            const removedJobs = [];
            for (let k = 0; k < ops.length; k++) {
                const op = ops[k];
                if (op.type === 'same') continue;
                const next = ops[k + 1];
                let dels = null, adds = null;
                if (op.type === 'del' && next && next.type === 'add') { dels = op; adds = next; k++; }
                else if (op.type === 'add' && next && next.type === 'del') { dels = next; adds = op; k++; }
                else if (op.type === 'del') dels = op;
                else adds = op;

                const pairs = dels && adds ? Math.min(dels.items.length, adds.items.length) : 0;
                for (let p = 0; p < pairs; p++) diffFields(adds.items[p], oldJobs[dels.items[p]], curJobs[adds.items[p]]);
                for (let p = pairs; dels && p < dels.items.length; p++) {
                    removedJobs.push(oldJobs[dels.items[p]]);
                    removedWords += Object.values(fieldValues(oldJobs[dels.items[p]])).reduce((sum, v) => sum + countWords(v), 0);
                }
                for (let p = pairs; adds && p < adds.items.length; p++) diffFields(adds.items[p], null, curJobs[adds.items[p]]);
            }
            return { diffs, removedJobs, added: addedWords, removed: removedWords };
        },
        // Highlighted html for one preview field, or the escaped plain value.
        fieldHtml(index, field, value) {
            const entry = this.diff && this.diff.diffs[index];
            return entry && entry[field] !== undefined ? entry[field] : this.escape(value);
        },
        addJob() {
            this.jobs.push({
                title: '',
                company: '',
                startDate: '',
                endDate: null,
                description: ''
            });
        },
        removeJob(index) {
            if (confirm('Are you sure you want to remove this entry?')) {
                this.jobs.splice(index, 1);
            }
        },
        moveJob(index, direction) {
            const target = index + direction;
            if (target < 0 || target >= this.jobs.length) return;
            const [job] = this.jobs.splice(index, 1);
            this.jobs.splice(target, 0, job);
        },
        sortJobs() {
            this.jobs.sort((a, b) => (a.startDate || '').localeCompare(b.startDate || ''));
        },
        // '2021-08-01' -> '08/2021', null-safe for the present-day entries.
        fmt(dateStr) {
            if (!dateStr) return null;
            const [year, month] = dateStr.split('-');
            return month + '/' + year;
        },
        get jobCount() { return this.jobs.length },
    }"
    x-on:cv-saved.window="saved = JSON.stringify(jobs)"
    x-on:keydown.meta.s.prevent="$wire.save()"
    x-on:keydown.ctrl.s.prevent="$wire.save()">
    @php($user = auth()->user())

    <x-page-header :title="__('CV Editor')">
        <x-slot:subtitle>
            <span x-show="changed" class="flex items-center gap-2 text-warning">
                <span class="size-2 rounded-full bg-warning"></span>
                {{ __('Unsaved changes') }}
            </span>
            <span x-show="!changed" x-cloak>{{ __('All changes saved') }}</span>
        </x-slot:subtitle>

        <x-ui.button variant="outline" :href="route('template', 'temp1')" target="_blank">
            <x-ui.icon name="external-link" /> {{ __('Open in template') }}
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
            <x-ui.card-header class="flex-row items-center justify-between">
                <x-ui.card-title>{{ __('Experience') }}</x-ui.card-title>
                <div class="flex items-center gap-2">
                    <x-ui.badge variant="muted"><span x-text="jobCount">0</span> {{ __('entries') }}</x-ui.badge>
                    <x-ui.button variant="ghost" size="sm" x-on:click="sortJobs()" title="{{ __('Sort by start date') }}">
                        {{ __('Sort by date') }}
                    </x-ui.button>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="flex flex-col gap-4">
                <template x-for="(job, index) in jobs" :key="index">
                    <div class="rounded-lg border bg-muted/30 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <x-ui.badge variant="secondary">
                                {{ __('Entry') }} <span x-text="index + 1"></span>
                            </x-ui.badge>
                            <div class="flex items-center gap-0.5">
                                <x-ui.button variant="ghost" size="icon-xs" x-on:click="moveJob(index, -1)" x-bind:disabled="index === 0" title="{{ __('Move up') }}">
                                    <x-ui.icon name="chevron-up" />
                                </x-ui.button>
                                <x-ui.button variant="ghost" size="icon-xs" x-on:click="moveJob(index, 1)" x-bind:disabled="index === jobs.length - 1" title="{{ __('Move down') }}">
                                    <x-ui.icon name="chevron-down" />
                                </x-ui.button>
                                <x-ui.button variant="ghost" size="icon-xs" class="hover:text-destructive" x-on:click="removeJob(index)" title="{{ __('Remove') }}">
                                    <x-ui.icon name="trash" />
                                </x-ui.button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <x-ui.field :label="__('Job Title')">
                                <x-ui.input x-model="job.title" placeholder="{{ __('Senior Frontend Developer') }}" />
                            </x-ui.field>
                            <x-ui.field :label="__('Company')">
                                <x-ui.input x-model="job.company" placeholder="{{ __('Nordic Tech ApS') }}" />
                            </x-ui.field>
                            <x-ui.field :label="__('Start Date')">
                                <x-ui.input type="date" x-model="job.startDate" />
                            </x-ui.field>
                            <x-ui.field :label="__('End Date')">
                                <x-ui.input type="date" x-model="job.endDate" x-bind:disabled="job.endDate === null" />
                                <label class="flex cursor-pointer items-center gap-2 text-sm text-muted-foreground">
                                    <x-ui.switch
                                        x-bind:checked="job.endDate === null"
                                        x-on:change="job.endDate = $event.target.checked ? null : ''"
                                    />
                                    {{ __('Current job') }}
                                </label>
                            </x-ui.field>
                        </div>

                        <x-ui.field :label="__('Description')" class="mt-3">
                            <x-ui.textarea x-model="job.description" rows="4" placeholder="{{ __('What did you do and achieve in this role?') }}" />
                        </x-ui.field>
                    </div>
                </template>

                <div x-show="jobs.length === 0" x-cloak>
                    <x-ui.empty icon="briefcase" :title="__('No experience entries yet.')" :description="__('Add your first job or education below.')" />
                </div>

                <x-ui.button variant="outline" x-on:click="addJob()">
                    <x-ui.icon name="plus" /> {{ __('Add entry') }}
                </x-ui.button>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Live preview --}}
        <x-ui.card class="lg:sticky lg:top-20">
            <x-ui.card-header class="flex-row items-center justify-between">
                <x-ui.card-title>{{ __('Preview') }}</x-ui.card-title>
                <template x-if="diffData">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="font-medium text-success">+<span x-text="diffData.added">0</span></span>
                        <span class="font-medium text-destructive">−<span x-text="diffData.removed">0</span></span>
                        <x-ui.button variant="ghost" size="xs" x-on:click="showDiff = !showDiff" x-text="showDiff ? '{{ __('Hide changes') }}' : '{{ __('Show changes') }}'" />
                    </div>
                </template>
            </x-ui.card-header>

            <x-ui.card-content class="space-y-3">
                {{-- The paper stays white in dark mode: it previews a printed document. --}}
                <div class="max-h-[297mm] overflow-y-auto rounded-lg border bg-white px-8 py-10 font-serif text-gray-900 shadow-inner">
                    <header class="border-b-2 border-gray-900 pb-6 text-center">
                        <h1 class="text-3xl font-bold uppercase tracking-wide">{{ $user->name }}</h1>
                        @if ($user->job_title)
                            <p class="mt-1 text-lg text-gray-700">{{ $user->job_title }}</p>
                        @endif
                        <p class="mt-3 text-sm text-gray-700">
                            {{ collect([$user->address, trim($user->zip.' '.$user->city), $user->phone, $user->email])->filter()->implode(' · ') }}
                        </p>
                    </header>

                    <section class="mt-8">
                        <h2 class="border-b border-gray-900 pb-1 text-sm font-bold uppercase tracking-widest">
                            {{ __('Erhvervserfaring & uddannelse') }}
                        </h2>
                        <div class="mt-5 space-y-5">
                            <template x-for="(job, index) in jobs" :key="index">
                                <div class="grid grid-cols-[150px_1fr] gap-4">
                                    <div class="whitespace-nowrap pt-0.5 text-sm text-gray-600">
                                        <span x-html="fieldHtml(index, 'start', fmt(job.startDate))"></span>
                                        –
                                        <span x-html="fieldHtml(index, 'end', job.endDate ? fmt(job.endDate) : '{{ __('nu') }}')"></span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold leading-snug" x-html="fieldHtml(index, 'title', job.title)"></h3>
                                        <p class="text-sm italic text-gray-600" x-html="fieldHtml(index, 'company', job.company)"></p>
                                        <p x-show="job.description"
                                            class="mt-1.5 whitespace-pre-wrap text-justify text-sm leading-relaxed"
                                            x-html="fieldHtml(index, 'description', job.description)"></p>
                                    </div>
                                </div>
                            </template>

                            {{-- Entries removed since the last save, only shown while the diff is on --}}
                            <template x-for="(job, r) in diff?.removedJobs || []" :key="'removed-' + r">
                                <div class="grid grid-cols-[150px_1fr] gap-4">
                                    <div class="whitespace-nowrap pt-0.5 text-sm text-gray-600">
                                        <span class="rounded-sm bg-red-100 text-red-900 line-through" x-text="fmt(job.startDate)"></span>
                                        –
                                        <span class="rounded-sm bg-red-100 text-red-900 line-through" x-text="job.endDate ? fmt(job.endDate) : '{{ __('nu') }}'"></span>
                                    </div>
                                    <div class="rounded-sm bg-red-100 text-red-900 line-through">
                                        <h3 class="font-bold leading-snug" x-text="job.title"></h3>
                                        <p class="text-sm italic text-gray-600" x-text="job.company"></p>
                                        <p x-show="job.description"
                                            class="mt-1.5 whitespace-pre-wrap text-justify text-sm leading-relaxed"
                                            x-text="job.description"></p>
                                    </div>
                                </div>
                            </template>
                            <p x-show="jobs.length === 0" class="text-sm italic text-gray-400">
                                {{ __('Your experience will appear here as you add entries...') }}
                            </p>
                        </div>
                    </section>

                    @if ($user->skills)
                        <section class="mt-8">
                            <h2 class="border-b border-gray-900 pb-1 text-sm font-bold uppercase tracking-widest">
                                {{ __('Kompetencer') }}
                            </h2>
                            <p class="mt-3 text-sm">{{ implode(', ', $user->skills) }}</p>
                        </section>
                    @endif

                    <section class="mt-8">
                        <h2 class="border-b border-gray-900 pb-1 text-sm font-bold uppercase tracking-widest">
                            {{ __('Personlige oplysninger') }}
                        </h2>
                        <dl class="mt-3 grid grid-cols-[140px_1fr] gap-y-1.5 text-sm">
                            @if ($user->birthdate)
                                <dt class="font-semibold">{{ __('Fødselsdato') }}</dt>
                                <dd>{{ $user->birthdate->format('d/m Y') }}</dd>
                            @endif
                            <dt class="font-semibold">{{ __('Adresse') }}</dt>
                            <dd>{{ collect([$user->address, $user->zip, $user->city])->filter()->implode(', ') }}</dd>
                            @if ($user->phone)
                                <dt class="font-semibold">{{ __('Telefon') }}</dt>
                                <dd>{{ $user->phone }}</dd>
                            @endif
                            <dt class="font-semibold">{{ __('E-mail') }}</dt>
                            <dd>{{ $user->email }}</dd>
                        </dl>
                    </section>
                </div>

                <p class="text-xs text-muted-foreground">
                    {{ __('Skills and personal details come from your profile — manage them there for now.') }}
                </p>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</div>
