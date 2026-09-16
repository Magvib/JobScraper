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
        init() {
            this.saved = JSON.stringify(this.jobs);
            this.hasLoaded = true;
        },
        get changed() {
            return this.hasLoaded && JSON.stringify(this.jobs) !== this.saved;
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

                                <label class="form-control mt-3">
                                    <span class="label-text mb-1 block text-sm opacity-70">{{ __('Description') }}</span>
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
                    <h2 class="card-title text-base">{{ __('Preview') }}</h2>

                    <div class="bg-white rounded-lg shadow-inner border border-base-300 px-8 py-10 font-serif text-gray-900 max-h-192 overflow-y-auto">
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
                                            <span x-text="fmt(job.startDate)"></span>
                                            –
                                            <span x-text="job.endDate ? fmt(job.endDate) : '{{ __('nu') }}'"></span>
                                        </div>
                                        <div>
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