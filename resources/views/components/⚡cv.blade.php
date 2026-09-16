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

<div class="py-10 mx-4"
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
    <div class="max-w-8xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold">CV Editor</h1>
                <p class="mt-2 opacity-70">
                    <span x-show="changed" class="text-warning flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-warning inline-block"></span>
                        Unsaved changes
                    </span>
                    <span x-show="!changed" x-cloak>All changes saved</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('template', 'temp1') }}" target="_blank" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    {{ __('Open in template') }}
                </a>
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
                    <div class="flex items-center justify-between">
                        <h2 class="card-title text-base">{{ __('Experience') }}</h2>
                        <div class="flex items-center gap-2">
                            <span class="badge badge-ghost"><span x-text="jobCount">0</span> {{ __('entries') }}</span>
                            <button type="button" class="btn btn-ghost btn-sm" @click="sortJobs()"
                                title="{{ __('Newest start date first') }}">
                                {{ __('Sort by date') }}
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4">
                        <template x-for="(job, index) in jobs" :key="index">
                            <div class="rounded-lg border border-base-300 bg-base-200/50 p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="badge badge-neutral">
                                        {{ __('Entry') }} <span x-text="index + 1"></span>
                                    </span>
                                    <div class="flex items-center gap-1">
                                        <button type="button" class="btn btn-ghost btn-xs" @click="moveJob(index, -1)"
                                            :disabled="index === 0" title="{{ __('Move up') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7" />
                                            </svg>
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-xs" @click="moveJob(index, 1)"
                                            :disabled="index === jobs.length - 1" title="{{ __('Move down') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-xs text-error"
                                            @click="removeJob(index)" title="{{ __('Remove') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <label class="form-control">
                                        <span class="label-text mb-1 block text-sm opacity-70">{{ __('Job Title') }}</span>
                                        <input type="text" x-model="job.title"
                                            class="input input-bordered w-full" placeholder="{{ __('Senior Frontend Developer') }}" />
                                    </label>
                                    <label class="form-control">
                                        <span class="label-text mb-1 block text-sm opacity-70">{{ __('Company') }}</span>
                                        <input type="text" x-model="job.company"
                                            class="input input-bordered w-full" placeholder="{{ __('Nordic Tech ApS') }}" />
                                    </label>
                                    <label class="form-control">
                                        <span class="label-text mb-1 block text-sm opacity-70">{{ __('Start Date') }}</span>
                                        <input type="date" x-model="job.startDate" class="input input-bordered w-full" />
                                    </label>
                                    <div>
                                        <span class="label-text mb-1 block text-sm opacity-70">{{ __('End Date') }}</span>
                                        <div class="join w-full">
                                            <input type="date" x-model="job.endDate"
                                                class="input input-bordered join-item w-full"
                                                :disabled="job.endDate === null" />
                                            <button type="button"
                                                class="btn join-item whitespace-nowrap"
                                                :class="job.endDate === null ? 'btn-primary' : 'btn-ghost'"
                                                @click="job.endDate = job.endDate === null ? '' : null"
                                                :aria-pressed="job.endDate === null">{{ __('Current job') }}</button>
                                        </div>
                                    </div>
                                </div>

                                <label class="form-control">
                                    <span class="label-text mb-1 mt-3 block text-sm opacity-70">{{ __('Description') }}</span>
                                    <textarea x-model="job.description" rows="4"
                                        class="textarea textarea-bordered w-full"
                                        placeholder="{{ __('What did you do and achieve in this role?') }}"></textarea>
                                </label>
                            </div>
                        </template>

                        <div x-show="jobs.length === 0" x-cloak
                            class="text-center py-10 border border-dashed border-base-300 rounded-lg text-base-content/50">
                            <p>{{ __('No experience entries yet.') }}</p>
                            <p class="text-sm mt-1">{{ __('Add your first job or education below.') }}</p>
                        </div>

                        <button type="button" class="btn btn-outline" @click="addJob()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('Add entry') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- Live preview --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="card-title text-base">{{ __('Preview') }}</h2>
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

                    <div class="bg-white rounded-lg shadow-inner border border-base-300 px-8 py-10 font-serif text-gray-900 max-h-[297mm] overflow-y-auto">
                        <header class="text-center pb-6 border-b-2 border-gray-900">
                            <h1 class="text-3xl font-bold tracking-wide uppercase">@auth {{ auth()->user()->name }} @endauth</h1>
                            @if (auth()->user()->job_title)
                                <p class="text-lg mt-1 text-gray-700">{{ auth()->user()->job_title }}</p>
                            @endif
                            <p class="text-sm mt-3 text-gray-700">
                                {{ collect([auth()->user()->address, trim(auth()->user()->zip . ' ' . auth()->user()->city), auth()->user()->phone, auth()->user()->email])->filter()->implode(' · ') }}
                            </p>
                        </header>

                        <section class="mt-8">
                            <h2 class="text-sm font-bold uppercase tracking-widest border-b border-gray-900 pb-1">
                                {{ __('Erhvervserfaring & uddannelse') }}
                            </h2>
                            <div class="mt-5 space-y-5">
                                <template x-for="(job, index) in jobs" :key="index">
                                    <div class="grid grid-cols-[150px_1fr] gap-4">
                                        <div class="text-sm text-gray-600 pt-0.5 whitespace-nowrap">
                                            <span x-html="fieldHtml(index, 'start', fmt(job.startDate))"></span>
                                            –
                                            <span x-html="fieldHtml(index, 'end', job.endDate ? fmt(job.endDate) : '{{ __('nu') }}')"></span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold leading-snug" x-html="fieldHtml(index, 'title', job.title)"></h3>
                                            <p class="text-sm italic text-gray-600" x-html="fieldHtml(index, 'company', job.company)"></p>
                                            <p x-show="job.description"
                                                class="text-sm mt-1.5 leading-relaxed text-justify whitespace-pre-wrap"
                                                x-html="fieldHtml(index, 'description', job.description)"></p>
                                        </div>
                                    </div>
                                </template>

                                {{-- Entries removed since the last save, only shown while the diff is on --}}
                                <template x-for="(job, r) in diff?.removedJobs || []" :key="'removed-' + r">
                                    <div class="grid grid-cols-[150px_1fr] gap-4">
                                        <div class="text-sm text-gray-600 pt-0.5 whitespace-nowrap">
                                            <span class="bg-red-100 text-red-900 rounded-sm line-through" x-text="fmt(job.startDate)"></span>
                                            –
                                            <span class="bg-red-100 text-red-900 rounded-sm line-through" x-text="job.endDate ? fmt(job.endDate) : '{{ __('nu') }}'"></span>
                                        </div>
                                        <div class="bg-red-100 text-red-900 rounded-sm line-through">
                                            <h3 class="font-bold leading-snug" x-text="job.title"></h3>
                                            <p class="text-sm italic text-gray-600" x-text="job.company"></p>
                                            <p x-show="job.description"
                                                class="text-sm mt-1.5 leading-relaxed text-justify whitespace-pre-wrap"
                                                x-text="job.description"></p>
                                        </div>
                                    </div>
                                </template>
                                <p x-show="jobs.length === 0" class="text-sm italic text-gray-400">
                                    {{ __('Your experience will appear here as you add entries...') }}
                                </p>
                            </div>
                        </section>

                        @if (auth()->user()->skills)
                            <section class="mt-8">
                                <h2 class="text-sm font-bold uppercase tracking-widest border-b border-gray-900 pb-1">
                                    {{ __('Kompetencer') }}
                                </h2>
                                <p class="mt-3 text-sm">{{ implode(', ', auth()->user()->skills) }}</p>
                            </section>
                        @endif

                        <section class="mt-8">
                            <h2 class="text-sm font-bold uppercase tracking-widest border-b border-gray-900 pb-1">
                                {{ __('Personlige oplysninger') }}
                            </h2>
                            <dl class="mt-3 grid grid-cols-[140px_1fr] gap-y-1.5 text-sm">
                                @if (auth()->user()->birthdate)
                                    <dt class="font-semibold">{{ __('Fødselsdato') }}</dt>
                                    <dd>{{ auth()->user()->birthdate->format('d/m Y') }}</dd>
                                @endif
                                <dt class="font-semibold">{{ __('Adresse') }}</dt>
                                <dd>{{ collect([auth()->user()->address, auth()->user()->zip, auth()->user()->city])->filter()->implode(', ') }}</dd>
                                @if (auth()->user()->phone)
                                    <dt class="font-semibold">{{ __('Telefon') }}</dt>
                                    <dd>{{ auth()->user()->phone }}</dd>
                                @endif
                                <dt class="font-semibold">{{ __('E-mail') }}</dt>
                                <dd>{{ auth()->user()->email }}</dd>
                            </dl>
                        </section>
                    </div>

                    <p class="text-xs opacity-60">
                        {{ __('Skills and personal details come from your profile — manage them there for now.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>