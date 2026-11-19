<?php

use App\Jobs\GenerateCoverLetter;
use App\Jobs\ProcessJobRating;
use App\Models\JobRating;
use App\Models\Post;
use App\Models\PostQuestionAnswer;
use App\Support\DanishCities;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/*
 * Performance notes
 * -----------------
 * - Everything data-heavy lives inside a lazy @island: the page shell renders instantly and the
 *   (potentially slow, first-visit) scrape + query runs in a follow-up request behind a skeleton.
 * - Jobs are NOT a public property any more. A public Eloquent collection is re-queried on every
 *   Livewire request and its ids are shipped in every payload; a computed property is built once per
 *   render instead. The ~200 entry city table moved to App\Support\DanishCities for the same reason.
 * - The query only selects the columns the list needs (no `raw` JSON / description HTML), and the
 *   user's ratings / answers are loaded with two queries instead of one per card.
 * - Coordinates and distances are resolved once per job per render (jobMeta) instead of several
 *   times per card, and only one page of cards is rendered at a time.
 * - A single poll on the island replaces one poll per pending card.
 */
new class extends Component
{
    private const PAGE_SIZE = 24;

    #[Url('q')]
    public string $search = '';

    #[Url('s')]
    public string $sort = 'date';

    #[Url('key')]
    public array $selectedKeywords = [];

    #[Url('src')]
    public array $selectedSources = [];

    /**
     * Question key => list of selected answer values, e.g.
     * ['remote' => ['yes'], 'salary' => ['high']].
     * Filters for questions no longer in the user's profile are ignored.
     */
    #[Url('qf')]
    public array $questionFilters = [];

    /**
     * Maximum distance in km between the user and a job. Editable
     * inline and saved back to the profile, like on the profile page.
     */
    public $maxDistance = 50;

    /**
     * Whether the distance filter is active. When off, every job is
     * listed no matter how far away it is.
     */
    #[Url('df')]
    public bool $filterByDistance = false;

    public bool $showMap = false;

    /** How many job cards are rendered ("Show more" raises this). */
    public int $visible = self::PAGE_SIZE;

    /**
     * Id of the job whose description is shown in the modal,
     * or null when the modal is closed.
     */
    public ?int $descriptionModalJobId = null;

    public function mount(): void
    {
        $this->maxDistance = auth()->user()->max_distance ?? 50;
    }

    /** Any filter/sort change starts again from the first page. */
    public function updated(string $property): void
    {
        $root = Str::before($property, '.');

        if (in_array($root, ['search', 'sort', 'selectedKeywords', 'selectedSources', 'questionFilters', 'filterByDistance'], true)) {
            $this->visible = self::PAGE_SIZE;
        }
    }

    /**
     * Save the inline-edited max distance to the profile, rejecting
     * values outside the same 1-500 km range the profile page enforces.
     */
    public function updatedMaxDistance($value): void
    {
        $value = trim((string) $value);

        if ($value === '' || ! is_numeric($value) || (int) $value < 1 || (int) $value > 500) {
            $this->maxDistance = auth()->user()->max_distance ?? 50;
            $this->dispatch(
                'toast',
                message: __('Max distance must be a number between 1 and 500 km.'),
                type: 'error'
            );

            return;
        }

        $this->maxDistance = (int) $value;
        auth()->user()->update(['max_distance' => $this->maxDistance]);
        $this->visible = self::PAGE_SIZE;
    }

    public function refreshJobs(): void
    {
        Post::searchForJobs(auth()->user(), true);
        $this->visible = self::PAGE_SIZE;
    }

    public function loadMore(): void
    {
        $this->visible += self::PAGE_SIZE;
    }

    /* ---------------------------------------------------------------------------------------------
     | Data
     * ------------------------------------------------------------------------------------------- */

    /**
     * Every active job for the user's keywords. Scrapes first when the 60 minute
     * cache is cold, which is why this is only ever touched from inside the lazy island.
     */
    #[Computed]
    public function jobs(): Collection
    {
        $user = auth()->user();
        $keywords = array_values(array_filter($user->keywords ?? []));

        if ($keywords === []) {
            return collect();
        }

        Post::searchForJobs($user);

        $posts = Post::query()
            ->active()
            ->whereIn('keyword', $keywords)
            ->select([
                'id', 'source', 'source_id', 'keyword', 'title', 'company_name', 'company_logo_url',
                'street', 'city', 'zipcode', 'country', 'latitude', 'longitude', 'canonical_url', 'published_at',
            ])
            ->selectRaw("(description IS NOT NULL AND description <> '') as has_description")
            ->get();

        // Post::rating() / questionAnswers() depend on $this->source, so they cannot be eager
        // loaded. Load the user's rows once and attach them by (source, source_id) instead.
        $ratings = JobRating::where('user_id', $user->id)->get()->keyBy(fn ($rating) => $rating->source.'|'.$rating->job_id);
        $answers = PostQuestionAnswer::where('user_id', $user->id)->get()->keyBy(fn ($answer) => $answer->source.'|'.$answer->job_id);

        foreach ($posts as $post) {
            $key = $post->source.'|'.$post->source_id;

            $post->setRelation('rating', $ratings->get($key));
            $post->setRelation('questionAnswers', $answers->get($key));
        }

        return $posts;
    }

    #[Computed]
    public function keywords(): Collection
    {
        return $this->jobs->pluck('keyword')->filter()->unique()->sort()->values();
    }

    #[Computed]
    public function sources(): Collection
    {
        return $this->jobs->pluck('source')->filter()->unique()->sort()->values();
    }

    #[Computed]
    public function hasQuestions(): bool
    {
        return ! empty(auth()->user()->questions);
    }

    #[Computed]
    public function hasDefaultCoverLetter(): bool
    {
        return auth()->user()->default_cover_letter_id !== null;
    }

    /**
     * The question definitions currently active on the user's profile.
     * Filters and filter buttons are derived from this list, so a question
     * deleted from the profile disappears from the filters even when old
     * answers for it still exist.
     */
    #[Computed]
    public function questionDefinitions(): Collection
    {
        return collect(auth()->user()->questions ?? [])->keyBy('key');
    }

    #[Computed]
    public function userPoint(): ?array
    {
        $user = auth()->user();

        if (! $user?->latitude || ! $user?->longitude) {
            return null;
        }

        return [
            'lat' => (float) $user->latitude,
            'lng' => (float) $user->longitude,
            'city' => $user->city,
            'maxDistance' => (float) ($this->maxDistance ?: ($user->max_distance ?? 50)),
        ];
    }

    /**
     * Coordinates and distance for every job, resolved once per render.
     * Job coordinates fall back to the city lookup when the ad has none.
     *
     * @return array<int, array{coords: ?array, distance: ?float}>
     */
    #[Computed]
    public function jobMeta(): array
    {
        $user = $this->userPoint;
        $meta = [];

        foreach ($this->jobs as $job) {
            $lat = $job->latitude;
            $lng = $job->longitude;

            if ((! $lat || ! $lng) && $job->city) {
                [$lat, $lng] = DanishCities::coordinates($job->city) ?? [null, null];
            }

            $coords = ($lat && $lng) ? [(float) $lat, (float) $lng] : null;

            $meta[$job->id] = [
                'coords' => $coords,
                'distance' => ($user !== null && $coords !== null)
                    ? $this->haversine($user['lat'], $user['lng'], $coords[0], $coords[1])
                    : null,
            ];
        }

        return $meta;
    }

    private function haversine(float $latFrom, float $lngFrom, float $latTo, float $lngTo): float
    {
        $latFrom = deg2rad($latFrom);
        $lngFrom = deg2rad($lngFrom);
        $latTo = deg2rad($latTo);
        $lngTo = deg2rad($lngTo);

        $a = sin(($latTo - $latFrom) / 2) ** 2 + cos($latFrom) * cos($latTo) * sin(($lngTo - $lngFrom) / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /** Latitude/longitude for a job, or null when nothing is known. */
    public function mapCoordinates($job): ?array
    {
        return $this->jobMeta[$job->id]['coords'] ?? null;
    }

    public function hasMapPoint($job): bool
    {
        return $this->mapCoordinates($job) !== null;
    }

    /** Distance in km between the user and a job, or null when either has no coordinates. */
    public function jobDistance($job): ?float
    {
        return $this->jobMeta[$job->id]['distance'] ?? null;
    }

    /**
     * Normalized filter value for a job's answer to a question definition:
     * 'yes'/'no' for booleans, the chosen option name for choices, and a
     * 'high'/'medium'/'low' score bucket — or null when unanswered.
     */
    public function questionAnswerValue($job, array $definition): ?string
    {
        $saved = $job->questionAnswers?->answers[$definition['key']] ?? null;

        if (! $saved) {
            return null;
        }

        return match ($definition['type']) {
            'boolean' => ($saved['answer']['probability'] ?? 0) >= 0.5 ? 'yes' : 'no',
            'choice' => $saved['answer']['choice'] ?? null,
            'score' => (function () use ($saved, $definition) {
                $pct = round(($saved['answer']['score'] ?? 0) * 100 / max(count($definition['levels'] ?? []) - 1, 1));

                return $pct >= 80 ? 'high' : ($pct >= 50 ? 'medium' : 'low');
            })(),
            default => null,
        };
    }

    /**
     * Filter buttons for the user's active questions, including only the
     * ones at least one listed job has an answer for.
     */
    #[Computed]
    public function questionFilterOptions(): Collection
    {
        return $this->questionDefinitions
            ->filter(fn ($definition) => $this->jobs->contains(
                fn ($job) => ($job->questionAnswers?->answers[$definition['key']] ?? null) !== null
            ))
            ->map(fn ($definition) => [
                'key' => $definition['key'],
                'question' => $definition['question'],
                'type' => $definition['type'],
                'values' => match ($definition['type']) {
                    'boolean' => [
                        ['value' => 'yes', 'label' => __('Yes'), 'title' => $definition['question']],
                        ['value' => 'no', 'label' => __('No'), 'title' => $definition['question']],
                    ],
                    'choice' => collect($definition['options'] ?? [])
                        ->map(fn ($description, $name) => [
                            'value' => $name,
                            'label' => $name,
                            'title' => trim($description ?? '') ?: $definition['question'],
                        ])
                        ->values()
                        ->all(),
                    'score' => [
                        ['value' => 'high', 'label' => __('80%+'), 'title' => $definition['question']],
                        ['value' => 'medium', 'label' => __('50-79%'), 'title' => $definition['question']],
                        ['value' => 'low', 'label' => __('<50%'), 'title' => $definition['question']],
                    ],
                    default => [],
                },
            ])
            ->values();
    }

    #[Computed]
    public function sortedJobs(): Collection
    {
        $jobs = $this->jobs;

        if ($this->selectedKeywords !== []) {
            $jobs = $jobs->filter(fn ($job) => in_array($job->keyword, $this->selectedKeywords));
        }

        if ($this->selectedSources !== []) {
            $jobs = $jobs->filter(fn ($job) => in_array($job->source, $this->selectedSources));
        }

        if (trim($this->search) !== '') {
            $needle = mb_strtolower(trim($this->search));

            $jobs = $jobs->filter(fn ($job) => str_contains(mb_strtolower(($job->title ?? '').' '.($job->company_name ?? '').' '.$job->getLocation()), $needle));
        }

        // Question filters only apply to definitions still active on the
        // profile; stale keys (deleted questions) are ignored.
        $definitions = $this->questionDefinitions;

        $activeFilters = collect($this->questionFilters)
            ->filter(fn ($values, $key) => $values !== [] && $definitions->has($key));

        if ($activeFilters->isNotEmpty()) {
            $jobs = $jobs->filter(function ($job) use ($activeFilters, $definitions) {
                foreach ($activeFilters as $key => $values) {
                    if (! in_array($this->questionAnswerValue($job, $definitions->get($key)), $values)) {
                        return false;
                    }
                }

                return true;
            });
        }

        $userPoint = $this->userPoint;

        if ($this->filterByDistance && $userPoint !== null) {
            $jobs = $jobs->filter(function ($job) use ($userPoint) {
                $distance = $this->jobDistance($job);

                return $distance !== null && $distance <= $userPoint['maxDistance'];
            });
        }

        return match ($this->sort) {
            'az' => $jobs->sortBy(fn ($job) => mb_strtolower($job->title))->values(),
            'score' => $jobs->sortByDesc(fn ($job) => $job->rating?->status === 'completed' ? $job->rating->skills_match : -1)->values(),
            'distance' => $userPoint === null
                ? $jobs->sortByDesc('published_at')->values()
                : $jobs->sortBy(fn ($job) => $this->jobDistance($job) ?? INF)->values(),
            default => $jobs->sortByDesc('published_at')->values(),
        };
    }

    /** The page of jobs that is actually rendered as cards. */
    #[Computed]
    public function visibleJobs(): Collection
    {
        return $this->sortedJobs->take($this->visible);
    }

    /** True while any rendered card still waits for an AI score or answers. */
    #[Computed]
    public function hasPending(): bool
    {
        return $this->visibleJobs->contains(
            fn ($job) => $job->rating?->status === 'pending' || $job->questionAnswers?->status === 'pending'
        );
    }

    #[Computed]
    public function mapPoints(): Collection
    {
        return $this->sortedJobs
            ->map(function ($job) {
                $coords = $this->mapCoordinates($job);

                if ($coords === null) {
                    return null;
                }

                return [
                    'lat' => $coords[0],
                    'lng' => $coords[1],
                    'title' => $job->title,
                    'company' => $job->company_name,
                    'location' => $job->getLocation(),
                    'url' => $job->canonical_url,
                    'keyword' => $job->keyword,
                    'color' => $this->keywordColor($job->keyword),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Identity of the rendered map. It ignores ordering so changing the sort keeps
     * the existing map (and its zoom) instead of rebuilding it.
     */
    #[Computed]
    public function mapKey(): string
    {
        return md5(
            $this->mapPoints->sortBy(fn ($p) => $p['lat'].','.$p['lng'].','.$p['title'])->values()->toJson()
            .'-'.(int) $this->maxDistance
        );
    }

    #[Computed]
    public function descriptionJob(): ?Post
    {
        return $this->descriptionModalJobId ? Post::find($this->descriptionModalJobId) : null;
    }

    /* ---------------------------------------------------------------------------------------------
     | View helpers
     * ------------------------------------------------------------------------------------------- */

    public function keywordColor(?string $keyword): ?string
    {
        if (! $keyword) {
            return null;
        }

        // Golden-angle hue from a stable keyword hash, so each keyword
        // gets a distinct color that never changes between reloads.
        $hue = fmod(crc32(mb_strtolower(trim($keyword))) * 137.508, 360);

        return 'hsl('.round($hue).', 70%, 45%)';
    }

    public function formatDistance(float $distance): string
    {
        return round($distance).' '.__('km');
    }

    /* ---------------------------------------------------------------------------------------------
     | Filter actions
     * ------------------------------------------------------------------------------------------- */

    public function toggleKeyword(string $keyword): void
    {
        $this->selectedKeywords = $this->toggled($this->selectedKeywords, $keyword);
        $this->visible = self::PAGE_SIZE;
    }

    public function toggleSource(string $source): void
    {
        $this->selectedSources = $this->toggled($this->selectedSources, $source);
        $this->visible = self::PAGE_SIZE;
    }

    public function toggleQuestionFilter(string $key, string $value): void
    {
        $selected = $this->toggled($this->questionFilters[$key] ?? [], $value);

        if ($selected === []) {
            $this->questionFilters = collect($this->questionFilters)->except($key)->all();
        } else {
            $this->questionFilters[$key] = $selected;
        }

        $this->visible = self::PAGE_SIZE;
    }

    public function toggleMap(): void
    {
        $this->showMap = ! $this->showMap;
    }

    private function toggled(array $values, string $value): array
    {
        return in_array($value, $values, true)
            ? array_values(array_diff($values, [$value]))
            : [...$values, $value];
    }

    /* ---------------------------------------------------------------------------------------------
     | Job actions
     * ------------------------------------------------------------------------------------------- */

    public function aiScore($postId)
    {
        $post = Post::find($postId);
        $user = auth()->user();

        if (! $post) {
            return null;
        }

        if ($post->rating) {
            $post->rating->delete();
        }

        $post->rating()->create([
            'user_id' => $user->id,
            'job_id' => $post->source_id,
            'source' => $post->source,
            'job_title' => $post->title,
            'job_url' => $post->canonical_url,
        ]);

        ProcessJobRating::dispatch($post, $user);

        $this->dispatch('toast',
            message: __('AI score is being calculated. Please check back in a few moments.'),
            type: 'success'
        );
    }

    public function generateCoverLetter($postId)
    {
        $post = Post::find($postId);
        $user = auth()->user();

        if (! $post) {
            return null;
        }

        if (! $user->defaultCoverLetter) {
            $this->dispatch('toast',
                message: __('You need to set a default cover letter on your profile first.'),
                type: 'error'
            );

            return null;
        }

        GenerateCoverLetter::dispatch($post, $user);

        $this->dispatch('toast',
            message: __('Cover letter is being generated. Please check back in a few moments.'),
            type: 'success'
        );
    }

    public function answerQuestions($postId)
    {
        $post = Post::find($postId);
        $user = auth()->user();

        if (! $post) {
            return null;
        }

        if (! $post->answerQuestions($user)) {
            $this->dispatch('toast',
                message: __('You need to define AI questions on your profile first.'),
                type: 'error'
            );

            return null;
        }

        $this->dispatch('toast',
            message: __('Your AI questions are being answered. Please check back in a few moments.'),
            type: 'success'
        );
    }

    public function answerAllQuestions()
    {
        $user = auth()->user();

        if (empty($user->questions)) {
            $this->dispatch('toast',
                message: __('You need to define AI questions on your profile first.'),
                type: 'error'
            );

            return null;
        }

        $count = 0;

        foreach ($this->sortedJobs as $job) {
            $job->answerQuestions($user);
            $count++;
        }

        $this->dispatch('toast',
            message: __('Your AI questions are being answered for :count jobs. Please check back in a few moments.', ['count' => $count]),
            type: 'success'
        );
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

    public function viewDescription($jobId)
    {
        $job = Post::find($jobId);

        if (! $job) {
            $this->dispatch('toast', message: __('Job not found.'), type: 'error');

            return;
        }

        if (! $job->description && ! $job->fetchDescription()) {
            $this->dispatch('toast', message: __('Could not fetch the job description.'), type: 'error');

            return;
        }

        $this->descriptionModalJobId = $job->id;
    }

    public function closeDescriptionModal(): void
    {
        $this->descriptionModalJobId = null;
    }
};
?>

<div>
    <x-page-header :title="__('Job Listings')" :description="__('Fresh listings for your keywords, ranked against your CV.')">
        <x-ui.button variant="outline" size="sm" :href="route('profile')" wire:navigate>
            <x-ui.icon name="filter" />
            {{ __('Change keywords or location') }}
        </x-ui.button>
        <x-ui.button
            variant="outline"
            size="sm"
            wire:click="refreshJobs"
            wire:island="jobs"
            wire:loading.attr="disabled"
            wire:target="refreshJobs"
        >
            <x-ui.icon name="refresh" wire:loading.class="animate-spin" wire:target="refreshJobs" />
            {{ __('Refresh') }}
        </x-ui.button>
    </x-page-header>

    {{-- How the AI ratings work. Collapsed by default, remembers its state per browser. --}}
    <x-ui.card size="flush" class="mb-6" x-data="{ open: $persist(false).as('jobs-ai-guide-open') }">
        <button type="button" class="flex w-full items-center gap-3 px-4 py-3 text-left" x-on:click="open = !open" x-bind:aria-expanded="open">
            <x-ui.icon name="info" class="text-muted-foreground" />
            <span class="grow text-sm font-medium">{{ __('How AI ratings work') }}</span>
            <x-ui.icon name="chevron-down" class="text-muted-foreground transition-transform" x-bind:class="open && 'rotate-180'" />
        </button>

        <div x-show="open" x-cloak class="space-y-3 border-t px-4 py-4">
            <p class="text-sm text-muted-foreground">
                {{ __('Scores compare your CV with each job posting. Click "AI Score" on a job to start the analysis. Tooltips on the badges explain the reasoning.') }}
            </p>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="flex items-center gap-3 rounded-lg border p-3 text-sm">
                    <div class="grow">
                        <div class="font-medium">{{ __('Skills Match') }}</div>
                        <div class="text-xs text-muted-foreground">{{ __('80%+ = strong alignment') }}</div>
                    </div>
                    <x-ui.badge variant="success">{{ __('80%+') }}</x-ui.badge>
                </div>
                <div class="flex items-center gap-3 rounded-lg border p-3 text-sm">
                    <div class="grow">
                        <div class="font-medium">{{ __('Experience Relevance') }}</div>
                        <div class="text-xs text-muted-foreground">{{ __('50-79% = partial match') }}</div>
                    </div>
                    <x-ui.badge variant="warning">{{ __('50-79%') }}</x-ui.badge>
                </div>
                <div class="flex items-center gap-3 rounded-lg border p-3 text-sm">
                    <div class="grow">
                        <div class="font-medium">{{ __('Seniority Fit') }}</div>
                        <div class="text-xs text-muted-foreground">{{ __('Under 50% = mismatch') }}</div>
                    </div>
                    <x-ui.badge variant="destructive">{{ __('<50%') }}</x-ui.badge>
                </div>
                <div class="flex items-center gap-3 rounded-lg border p-3 text-sm">
                    <div class="grow">
                        <div class="font-medium">{{ __('Keyword Match (ATS)') }}</div>
                        <div class="text-xs text-muted-foreground">{{ __('Checks required terms') }}</div>
                    </div>
                    <x-ui.badge variant="info">{{ __('ATS') }}</x-ui.badge>
                </div>
            </div>
            <p class="text-xs text-muted-foreground">
                {{ __('New scores appear as "Calculating..." and update automatically. Re-run "AI Score" if you update your CV or keywords.') }}
            </p>
        </div>
    </x-ui.card>

    {{--
        Lazy island: the shell above renders immediately, the scrape + query + cards load right after.
        Interactions inside the island only re-render the island, never the whole component.
    --}}
    @island(name: 'jobs', lazy: true)
        @placeholder
            <div class="space-y-4" aria-busy="true">
                <p class="flex items-center gap-2 text-sm text-muted-foreground">
                    <x-ui.spinner /> {{ __('Fetching the latest listings…') }}
                </p>
                <x-ui.skeleton class="h-24 w-full" />
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    @foreach (range(1, 6) as $i)
                        <x-ui.skeleton class="h-52 w-full" />
                    @endforeach
                </div>
            </div>
        @endplaceholder

        <div class="space-y-4" @if ($this->hasPending) wire:poll.5s @endif>
            @if ($this->jobs->isEmpty())
                <x-ui.empty icon="search" :title="__('No jobs found')" :description="__('Try updating your search keywords in your profile.')">
                    <x-ui.button :href="route('profile')" wire:navigate>{{ __('Update Profile') }}</x-ui.button>
                </x-ui.empty>
            @else
                {{-- Toolbar --}}
                <x-ui.card size="sm">
                    <x-ui.card-content class="flex flex-col gap-3">
                        <div class="flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-center">
                            <div class="relative min-w-0 grow lg:min-w-64">
                                <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                <x-ui.input
                                    type="search"
                                    wire:model.live.debounce.300ms="search"
                                    placeholder="{{ __('Search title, company or location...') }}"
                                    class="pl-9"
                                />
                            </div>

                            <x-ui.toggle-group>
                                @foreach (['date' => __('Date'), 'az' => __('A-Z'), 'score' => __('AI score'), 'distance' => __('Distance')] as $sortKey => $sortLabel)
                                    <x-ui.toggle-item :active="$this->sort === $sortKey" wire:click="$set('sort', '{{ $sortKey }}')">{{ $sortLabel }}</x-ui.toggle-item>
                                @endforeach
                            </x-ui.toggle-group>

                            @if ($this->userPoint)
                                <div
                                    class="flex h-9 shrink-0 items-center gap-2 rounded-md border bg-background px-3 shadow-xs dark:bg-input/30"
                                    title="{{ __('Only show jobs within this distance of your location. The distance is saved to your profile.') }}"
                                >
                                    <x-ui.switch wire:model.live="filterByDistance" aria-label="{{ __('Filter jobs by distance') }}" />
                                    <input
                                        type="number"
                                        wire:model.live.debounce.500ms="maxDistance"
                                        class="w-12 bg-transparent text-sm tabular-nums outline-none"
                                        min="1"
                                        max="500"
                                        aria-label="{{ __('Max distance (km)') }}"
                                    />
                                    <span class="whitespace-nowrap text-xs text-muted-foreground">{{ __('km max') }}</span>
                                </div>
                            @endif

                            <x-ui.button :variant="$this->showMap ? 'default' : 'outline'" wire:click="toggleMap">
                                <x-ui.icon name="map-pin" /> {{ __('Map') }}
                            </x-ui.button>

                            @if ($this->hasQuestions)
                                <x-ui.button
                                    variant="outline"
                                    wire:click="answerAllQuestions"
                                    wire:loading.attr="disabled"
                                    wire:target="answerAllQuestions"
                                    title="{{ __('Answer your AI questions for every job currently shown') }}"
                                >
                                    <x-ui.icon name="circle-help" /> {{ __('Answer Questions') }}
                                </x-ui.button>
                            @endif
                        </div>

                        @if ($this->keywords->count() > 1)
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="w-16 text-xs font-medium text-muted-foreground">{{ __('Keyword') }}</span>
                                <x-ui.chip :active="$this->selectedKeywords === []" wire:click="$set('selectedKeywords', [])">{{ __('All') }}</x-ui.chip>
                                @foreach ($this->keywords as $kw)
                                    <x-ui.chip :active="in_array($kw, $this->selectedKeywords)" wire:click="toggleKeyword('{{ $kw }}')" wire:key="kw-{{ $kw }}">
                                        <span class="size-2 shrink-0 rounded-full" style="background: {{ $this->keywordColor($kw) }}" title="{{ __('Map color') }}"></span>
                                        {{ Str::title($kw) }}
                                    </x-ui.chip>
                                @endforeach
                            </div>
                        @endif

                        @if ($this->sources->count() > 1)
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="w-16 text-xs font-medium text-muted-foreground">{{ __('Source') }}</span>
                                <x-ui.chip :active="$this->selectedSources === []" wire:click="$set('selectedSources', [])">{{ __('All') }}</x-ui.chip>
                                @foreach ($this->sources as $src)
                                    <x-ui.chip :active="in_array($src, $this->selectedSources)" wire:click="toggleSource('{{ $src }}')" wire:key="src-{{ $src }}">
                                        {{ Str::title($src) }}
                                    </x-ui.chip>
                                @endforeach
                            </div>
                        @endif

                        @if ($this->questionFilterOptions->isNotEmpty())
                            <div class="flex flex-col gap-2">
                                @foreach ($this->questionFilterOptions as $filter)
                                    <div class="flex flex-wrap items-center gap-2" wire:key="qf-{{ $filter['key'] }}">
                                        <span class="w-16 truncate text-xs font-medium text-muted-foreground" title="{{ $filter['question'] }}">
                                            {{ Str::title(str_replace('_', ' ', $filter['key'])) }}
                                        </span>
                                        <x-ui.chip :active="($this->questionFilters[$filter['key']] ?? []) === []" wire:click="$set('questionFilters.{{ $filter['key'] }}', [])">{{ __('All') }}</x-ui.chip>
                                        @foreach ($filter['values'] as $value)
                                            <x-ui.chip
                                                :active="in_array($value['value'], $this->questionFilters[$filter['key']] ?? [])"
                                                title="{{ $value['title'] }}"
                                                wire:click="toggleQuestionFilter('{{ $filter['key'] }}', '{{ $value['value'] }}')"
                                            >{{ $value['label'] }}</x-ui.chip>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </x-ui.card-content>
                </x-ui.card>

                <p class="text-sm text-muted-foreground">
                    @if ($this->sortedJobs->count() === $this->jobs->count())
                        {{ number_format($this->jobs->count()) }} {{ __('jobs found') }}
                    @else
                        {{ number_format($this->sortedJobs->count()) }} / {{ number_format($this->jobs->count()) }} {{ __('shown') }}
                    @endif
                </p>

                {{-- Map (Leaflet is wired up in resources/js/jobs-map.js) --}}
                @if ($this->showMap)
                    <x-ui.card size="flush" id="jobs-map-card" wire:key="jobs-map-{{ $this->mapKey }}">
                        <div class="jobs-map-body flex flex-col gap-3 p-4">
                            <div class="flex items-center justify-between gap-2">
                                <div class="jobs-map-filters min-w-0 flex-wrap items-center gap-x-3 gap-y-2">
                                    @if ($this->keywords->count() > 1)
                                        <div class="flex flex-wrap items-center gap-1">
                                            <span class="text-xs text-muted-foreground">{{ __('Keyword') }}:</span>
                                            <x-ui.chip :active="$this->selectedKeywords === []" wire:click="$set('selectedKeywords', [])">{{ __('All') }}</x-ui.chip>
                                            @foreach ($this->keywords as $kw)
                                                <x-ui.chip :active="in_array($kw, $this->selectedKeywords)" wire:click="toggleKeyword('{{ $kw }}')">
                                                    <span class="size-2 shrink-0 rounded-full" style="background: {{ $this->keywordColor($kw) }}"></span>
                                                    {{ Str::title($kw) }}
                                                </x-ui.chip>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($this->sources->count() > 1)
                                        <div class="flex flex-wrap items-center gap-1">
                                            <span class="text-xs text-muted-foreground">{{ __('Source') }}:</span>
                                            <x-ui.chip :active="$this->selectedSources === []" wire:click="$set('selectedSources', [])">{{ __('All') }}</x-ui.chip>
                                            @foreach ($this->sources as $src)
                                                <x-ui.chip :active="in_array($src, $this->selectedSources)" wire:click="toggleSource('{{ $src }}')">{{ Str::title($src) }}</x-ui.chip>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-1">
                                        <span class="text-xs text-muted-foreground">{{ __('Sort') }}:</span>
                                        <x-ui.toggle-group>
                                            @foreach (['date' => __('Date'), 'az' => __('A-Z'), 'score' => __('AI score'), 'distance' => __('Distance')] as $sortKey => $sortLabel)
                                                <x-ui.toggle-item size="sm" :active="$this->sort === $sortKey" wire:click="$set('sort', '{{ $sortKey }}')">{{ $sortLabel }}</x-ui.toggle-item>
                                            @endforeach
                                        </x-ui.toggle-group>
                                    </div>
                                    <span class="whitespace-nowrap text-xs text-muted-foreground">
                                        {{ number_format($this->mapPoints->count()) }} / {{ number_format($this->jobs->count()) }} {{ __('shown') }}
                                    </span>
                                </div>

                                <div class="jobs-map-header-text">
                                    <p class="text-sm font-semibold">{{ __('Job locations') }}</p>
                                    <span class="text-xs text-muted-foreground">
                                        {{ number_format($this->mapPoints->count()) }} / {{ number_format($this->sortedJobs->count()) }} {{ __('jobs have a known location') }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-ui.button id="jobs-map-expand" variant="ghost" size="sm" title="{{ __('Expand map') }}">
                                        <x-ui.icon name="maximize" /> {{ __('Expand') }}
                                    </x-ui.button>
                                    <x-ui.button id="jobs-map-exit" variant="ghost" size="icon-sm" title="{{ __('Close map') }}" aria-label="{{ __('Close map') }}">
                                        <x-ui.icon name="x" />
                                    </x-ui.button>
                                </div>
                            </div>

                            @if ($this->mapPoints->isEmpty())
                                <p class="text-sm text-muted-foreground">{{ __('None of the listed jobs have a known location.') }}</p>
                            @else
                                @if ($this->mapPoints->pluck('keyword')->filter()->unique()->count() > 1)
                                    <div class="jobs-map-legend flex flex-wrap items-center gap-2">
                                        <span class="text-xs text-muted-foreground">{{ __('Keyword colors') }}:</span>
                                        @foreach ($this->mapPoints->pluck('keyword')->filter()->unique()->sort() as $kw)
                                            <x-ui.badge variant="muted" class="gap-1.5">
                                                <span class="size-2.5 rounded-full" style="background: {{ $this->keywordColor($kw) }}"></span>
                                                {{ Str::title($kw) }}
                                            </x-ui.badge>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="jobs-map-layout">
                                    <script type="application/json" id="jobs-map-data">@json($this->mapPoints)</script>
                                    <script type="application/json" id="jobs-map-user-data">@json($this->userPoint)</script>
                                    <div id="jobs-map" class="z-0 h-[30rem] overflow-hidden rounded-lg border"></div>
                                    <div id="jobs-map-sidebar" class="hidden flex-col border-l pl-3">
                                        <div class="sticky top-0 z-10 shrink-0 bg-card py-1 pr-1">
                                            <span class="text-xs text-muted-foreground">
                                                {{ __('In view') }}: <span id="jobs-map-sidebar-count">0</span>
                                            </span>
                                        </div>
                                        <div id="jobs-map-sidebar-list" class="flex flex-col gap-2">
                                            @foreach ($this->sortedJobs as $job)
                                                @php($coords = $this->mapCoordinates($job))
                                                @if ($coords !== null)
                                                    @php($distance = $this->jobDistance($job))
                                                    <a
                                                        href="{{ $job->canonical_url }}"
                                                        target="_blank"
                                                        rel="noopener"
                                                        class="jobs-map-sidebar-item shrink-0 rounded-lg border bg-card p-3 transition-colors hover:border-ring/60"
                                                        data-map-lat="{{ $coords[0] }}"
                                                        data-map-lng="{{ $coords[1] }}"
                                                        data-map-color="{{ $this->keywordColor($job->keyword) }}"
                                                    >
                                                        <div class="flex items-start gap-2">
                                                            @if ($job->keyword)
                                                                <span class="mt-1.5 size-2.5 shrink-0 rounded-full" style="background: {{ $this->keywordColor($job->keyword) }}" title="{{ __('Map color') }}"></span>
                                                            @endif
                                                            <span class="line-clamp-2 text-sm font-medium">{{ $job->title }}</span>
                                                        </div>
                                                        <div class="mt-1 text-xs text-muted-foreground">{{ $job->company_name }}</div>
                                                        <div class="flex flex-wrap items-center gap-x-1.5 text-xs text-muted-foreground">
                                                            <span>{{ $job->getLocation() }}</span>
                                                            @if ($distance !== null)
                                                                <span class="opacity-70">({{ $this->formatDistance($distance) }})</span>
                                                            @endif
                                                            <span class="opacity-70">·</span>
                                                            <span>{{ $job->published_at?->diffForHumans() }}</span>
                                                        </div>
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                        <p id="jobs-map-sidebar-empty" class="m-0 hidden py-6 text-center text-sm text-muted-foreground">
                                            {{ __('No jobs in this area. Pan or zoom the map.') }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </x-ui.card>
                @endif

                {{-- Job cards --}}
                @if ($this->sortedJobs->isEmpty())
                    <x-ui.empty icon="search" :title="__('No jobs match your search or filters.')" />
                @else
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        @foreach ($this->visibleJobs as $job)
                            @php($distance = $this->jobDistance($job))
                            <x-ui.card size="sm" class="transition-colors hover:border-ring/50" wire:key="job-{{ $job->id }}">
                                <x-ui.card-content class="flex h-full flex-col gap-4">
                                    <div class="flex items-start gap-3">
                                        <x-ui.avatar :src="$job->company_logo_url" :name="$job->company_name" size="size-11" square />
                                        <div class="min-w-0 grow">
                                            <a href="{{ $job->canonical_url }}" target="_blank" rel="noopener" class="line-clamp-2 font-semibold leading-snug hover:underline">
                                                {{ $job->title }}
                                            </a>
                                            <div class="mt-0.5 text-sm text-muted-foreground">{{ $job->company_name }}</div>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-muted-foreground">
                                        <x-ui.badge variant="secondary">{{ Str::title($job->source ?? 'Jobindex') }}</x-ui.badge>
                                        @if ($job->keyword)
                                            <x-ui.badge variant="outline" class="gap-1.5" title="{{ __('Search keyword') }}">
                                                <span class="size-2 shrink-0 rounded-full" style="background: {{ $this->keywordColor($job->keyword) }}"></span>
                                                {{ Str::title($job->keyword) }}
                                            </x-ui.badge>
                                        @endif
                                        <span class="inline-flex items-center gap-1.5">
                                            <x-ui.icon name="map-pin" />
                                            {{ $job->getLocation() }}
                                            @if ($distance !== null)
                                                <span class="opacity-70">({{ $this->formatDistance($distance) }})</span>
                                            @endif
                                        </span>
                                        @if (! $this->hasMapPoint($job))
                                            <x-ui.badge variant="warning" title="{{ __('This job has no known coordinates, so it is not shown on the map') }}">{{ __('Not on map') }}</x-ui.badge>
                                        @endif
                                        <span class="inline-flex items-center gap-1.5">
                                            <x-ui.icon name="clock" />
                                            {{ $job->published_at?->diffForHumans() }}
                                        </span>
                                    </div>

                                    @if ($job->rating || $job->questionAnswers)
                                        <div class="flex flex-col gap-2 lg:flex-row">
                                            @if ($job->rating)
                                                <div class="min-w-0 flex-1 rounded-lg border bg-muted/40 px-3 py-2">
                                                    <div class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">{{ __('AI Score') }}</div>
                                                    @if ($job->rating->status === 'pending')
                                                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                                            <x-ui.spinner /> {{ __('Calculating...') }}
                                                        </div>
                                                    @elseif ($job->rating->status === 'failed')
                                                        <x-ui.badge variant="destructive">{{ __('Failed') }}</x-ui.badge>
                                                    @else
                                                        <div class="flex flex-wrap gap-x-4 gap-y-1.5">
                                                            @foreach (['skills_match' => __('Skills'), 'experience_relevance' => __('Experience'), 'seniority_fit' => __('Seniority'), 'keyword_match' => __('Keywords')] as $field => $label)
                                                                <x-ui.tooltip :text="$job->rating->{$field.'_reasoning'}">
                                                                    <x-score-badge :value="$job->rating->{$field}" :label="$label" />
                                                                </x-ui.tooltip>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            @if ($job->questionAnswers)
                                                <div class="min-w-0 flex-1 rounded-lg border bg-muted/40 px-3 py-2">
                                                    <div class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">{{ __('Your Questions') }}</div>
                                                    @if ($job->questionAnswers->status === 'pending')
                                                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                                            <x-ui.spinner /> {{ __('Answering...') }}
                                                        </div>
                                                    @elseif ($job->questionAnswers->status === 'failed')
                                                        <x-ui.badge variant="destructive">{{ __('Answers failed') }}</x-ui.badge>
                                                    @else
                                                        <div class="flex flex-wrap gap-x-4 gap-y-1.5">
                                                            @foreach ($this->questionDefinitions as $definition)
                                                                @php($saved = $job->questionAnswers->answers[$definition['key']] ?? null)
                                                                @if ($saved)
                                                                    <x-ui.tooltip :text="$definition['question']">
                                                                        <span class="inline-flex items-center gap-1.5">
                                                                            <span class="truncate text-xs text-muted-foreground">{{ Str::title(str_replace('_', ' ', $definition['key'])) }}</span>
                                                                            @if ($saved['type'] === 'boolean')
                                                                                @php($yes = ($saved['answer']['probability'] ?? 0) >= 0.5)
                                                                                <x-ui.badge :variant="$yes ? 'success' : 'destructive'">{{ $yes ? __('Yes') : __('No') }}</x-ui.badge>
                                                                            @elseif ($saved['type'] === 'choice')
                                                                                <x-ui.badge variant="secondary">{{ $saved['answer']['choice'] ?? '—' }}</x-ui.badge>
                                                                            @else
                                                                                <x-score-badge :value="round(($saved['answer']['score'] ?? 0) * 100 / max(count($definition['levels'] ?? []) - 1, 1))" />
                                                                            @endif
                                                                        </span>
                                                                    </x-ui.tooltip>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-1">
                                        <div class="flex flex-wrap items-center gap-1">
                                            <x-ui.button
                                                variant="ghost"
                                                size="xs"
                                                wire:click="aiScore({{ $job->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="aiScore({{ $job->id }})"
                                                title="{{ __('Compare this job with your CV') }}"
                                            >
                                                <x-ui.icon name="sparkles" size="size-3.5" /> {{ __('AI Score') }}
                                            </x-ui.button>

                                            @if ($this->hasQuestions)
                                                <x-ui.button
                                                    variant="ghost"
                                                    size="xs"
                                                    wire:click="answerQuestions({{ $job->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="answerQuestions({{ $job->id }})"
                                                    title="{{ __('Answer your AI questions about this job') }}"
                                                >
                                                    <x-ui.icon name="circle-help" size="size-3.5" /> {{ __('Questions') }}
                                                </x-ui.button>
                                            @endif

                                            @if ($this->hasDefaultCoverLetter)
                                                <x-ui.button
                                                    variant="ghost"
                                                    size="xs"
                                                    wire:click="generateCoverLetter({{ $job->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="generateCoverLetter({{ $job->id }})"
                                                    title="{{ __('Generate a tailored cover letter') }}"
                                                >
                                                    <x-ui.icon name="file-text" size="size-3.5" /> {{ __('Cover Letter') }}
                                                </x-ui.button>
                                            @endif

                                            @if ($job->has_description)
                                                <x-ui.button variant="ghost" size="xs" wire:click="viewDescription({{ $job->id }})">
                                                    {{ __('Description') }}
                                                    <x-ui.spinner size="size-3" wire:loading wire:target="viewDescription({{ $job->id }})" />
                                                </x-ui.button>
                                            @else
                                                <x-ui.button variant="ghost" size="xs" class="text-destructive hover:text-destructive" wire:click="getDescription({{ $job->id }})">
                                                    {{ __('Get Description') }}
                                                    <x-ui.spinner size="size-3" wire:loading wire:target="getDescription({{ $job->id }})" />
                                                </x-ui.button>
                                            @endif
                                        </div>

                                        <x-ui.button size="sm" :href="$job->canonical_url" target="_blank" rel="noopener">
                                            {{ __('View Job') }} <x-ui.icon name="external-link" />
                                        </x-ui.button>
                                    </div>
                                </x-ui.card-content>
                            </x-ui.card>
                        @endforeach
                    </div>

                    @if ($this->sortedJobs->count() > $this->visible)
                        <div class="flex flex-col items-center gap-2 pt-2">
                            <p class="text-xs text-muted-foreground">
                                {{ __('Showing :shown of :total jobs', ['shown' => number_format($this->visibleJobs->count()), 'total' => number_format($this->sortedJobs->count())]) }}
                            </p>
                            <x-ui.button variant="outline" wire:click="loadMore" wire:loading.attr="disabled" wire:target="loadMore">
                                <x-ui.spinner wire:loading wire:target="loadMore" />
                                {{ __('Show more') }}
                            </x-ui.button>
                        </div>
                    @endif
                @endif

                {{-- Description modal --}}
                @if ($this->descriptionJob)
                    <x-ui.dialog close="$wire.closeDescriptionModal()">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="line-clamp-2 text-lg font-semibold leading-snug">{{ $this->descriptionJob->title }}</h3>
                                <p class="mt-0.5 text-sm text-muted-foreground">{{ $this->descriptionJob->company_name }} · {{ $this->descriptionJob->getLocation() }}</p>
                            </div>
                            <x-ui.button variant="ghost" size="icon-sm" wire:click="closeDescriptionModal" aria-label="{{ __('Close') }}">
                                <x-ui.icon name="x" />
                            </x-ui.button>
                        </div>

                        <div class="job-desc max-h-[60vh] overflow-y-auto rounded-lg border bg-muted/40 p-4 text-sm leading-relaxed">
                            {!! $this->descriptionJob->safeDescription() !!}
                        </div>

                        <div class="flex flex-wrap justify-end gap-2">
                            <x-ui.button variant="outline" size="sm" wire:click="getDescription({{ $this->descriptionJob->id }}, true)">
                                {{ __('Reload Description') }}
                                <x-ui.spinner size="size-3" wire:loading wire:target="getDescription({{ $this->descriptionJob->id }}, true)" />
                            </x-ui.button>
                            <x-ui.button size="sm" :href="$this->descriptionJob->canonical_url" target="_blank" rel="noopener">{{ __('View Job') }}</x-ui.button>
                        </div>
                    </x-ui.dialog>
                @endif
            @endif
        </div>
    @endisland
</div>
