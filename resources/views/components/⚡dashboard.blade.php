<?php

use App\Models\JobRating;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $username = '';

    public function mount(): void
    {
        $this->username = auth()->user()->name;
    }

    /**
     * All of the user's ratings, loaded once per render. The stats, top matches
     * and recent activity are all derived from this single query (they used to be
     * three separate accessors that re-ran their queries on every read).
     */
    #[Computed]
    public function ratings(): Collection
    {
        return JobRating::where('user_id', auth()->id())->get();
    }

    #[Computed]
    public function stats(): array
    {
        $ratings = $this->ratings;
        $completed = $ratings->where('status', 'completed');
        $scores = $completed->map(fn ($rating) => $this->scoreFor($rating));

        return [
            'total' => $ratings->count(),
            'completed' => $completed->count(),
            'pending' => $ratings->where('status', 'pending')->count(),
            'failed' => $ratings->where('status', 'failed')->count(),
            'average' => $scores->isNotEmpty() ? round($scores->avg()) : null,
            'strong' => $scores->filter(fn ($score) => $score >= 80)->count(),
            'this_week' => $ratings->where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }

    #[Computed]
    public function topJobs(): Collection
    {
        return $this->ratings
            ->where('status', 'completed')
            ->filter(fn ($rating) => $rating->job_title !== null)
            ->map(function ($rating) {
                $rating->score = $this->scoreFor($rating);

                return $rating;
            })
            ->sortByDesc('score')
            ->take(5)
            ->values();
    }

    #[Computed]
    public function recentActivity(): Collection
    {
        return $this->ratings->sortByDesc('updated_at')->take(5)->values();
    }

    private function scoreFor(JobRating $rating): float
    {
        return collect([
            $rating->skills_match,
            $rating->experience_relevance,
            $rating->seniority_fit,
            $rating->keyword_match,
        ])->filter()->avg() ?? 0;
    }
};
?>

<div>
    @php
        $user = auth()->user();
        $stats = $this->stats;
        $average = $stats['average'];
    @endphp

    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4">
            <x-ui.avatar :src="$user->getImage()" :name="$user->name" size="size-12" />
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight">{{ __('Welcome back, :name!', ['name' => $username]) }}</h1>
                <p class="text-sm text-muted-foreground">{{ __("Here's how your job search is going") }}</p>
            </div>
        </div>

        <x-ui.button :href="route('jobs')" wire:navigate>
            <x-ui.icon name="search" /> {{ __('Browse Jobs') }}
        </x-ui.button>
    </div>

    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat-card
            :label="__('Jobs Matched')"
            :value="$stats['completed']"
            :hint="$stats['pending'] > 0 ? __(':count still calculating', ['count' => $stats['pending']]) : __('rated by AI so far')"
        />
        <x-stat-card
            :label="__('Average Match')"
            :value="$average !== null ? $average.'%' : '—'"
            :tone="$average === null ? null : ($average >= 80 ? 'success' : ($average >= 50 ? 'warning' : 'destructive'))"
            :hint="__('across all rated jobs')"
        />
        <x-stat-card :label="__('Strong Matches')" :value="$stats['strong']" tone="success" :hint="__('scoring 80% or above')" />
        <x-stat-card
            :label="__('New This Week')"
            :value="$stats['this_week']"
            tone="info"
            :hint="$stats['failed'] > 0 ? trans_choice(':count failed rating|:count failed ratings', $stats['failed']) : __('matched in the last 7 days')"
        />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2">
            <x-ui.card-header class="flex-row items-center justify-between">
                <x-ui.card-title class="text-lg">{{ __('Top Matches') }}</x-ui.card-title>
                <x-ui.button variant="link" size="sm" :href="route('jobs')" wire:navigate>
                    {{ __('View all jobs') }} <x-ui.icon name="arrow-right" />
                </x-ui.button>
            </x-ui.card-header>

            <x-ui.card-content>
                @if ($this->topJobs->isEmpty())
                    <x-ui.empty
                        icon="target"
                        :title="__('No scored jobs yet')"
                        :description="__('Use the AI Score button on the Jobs page, or enable auto-match in your profile, to get your first matches.')"
                    />
                @else
                    <div class="divide-y">
                        @foreach ($this->topJobs as $i => $job)
                            <div class="flex items-center gap-4 py-3 first:pt-0 last:pb-0">
                                <div @class([
                                    'flex size-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold',
                                    'bg-warning/15 text-warning' => $i === 0,
                                    'bg-muted text-muted-foreground' => $i !== 0,
                                ])>{{ $i + 1 }}</div>

                                <div class="min-w-0 grow">
                                    <a href="{{ $job->job_url }}" target="_blank" rel="noopener" class="line-clamp-1 font-medium hover:underline">
                                        {{ $job->job_title }}
                                    </a>
                                    <div class="mt-0.5 text-xs text-muted-foreground">{{ __('Rated :time', ['time' => $job->updated_at->diffForHumans()]) }}</div>
                                </div>

                                <div class="hidden shrink-0 items-center gap-1 md:flex">
                                    @foreach (['skills_match' => __('Skills'), 'experience_relevance' => __('Experience'), 'seniority_fit' => __('Seniority'), 'keyword_match' => __('Keywords')] as $field => $label)
                                        <x-ui.tooltip :text="$label.': '.number_format($job->{$field} ?? 0, 0).'%'">
                                            <x-ui.badge variant="muted" class="tabular-nums">{{ number_format($job->{$field} ?? 0, 0) }}%</x-ui.badge>
                                        </x-ui.tooltip>
                                    @endforeach
                                </div>

                                <x-score-badge :value="$job->score" class="shrink-0 px-2.5 py-1 text-sm" />
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card-content>
        </x-ui.card>

        <x-ui.card>
            <x-ui.card-header>
                <x-ui.card-title class="text-lg">{{ __('Recent Activity') }}</x-ui.card-title>
            </x-ui.card-header>

            <x-ui.card-content>
                @if ($this->recentActivity->isEmpty())
                    <p class="py-8 text-center text-sm text-muted-foreground">{{ __("No activity yet. Once jobs are scored, they'll show up here.") }}</p>
                @else
                    <div class="space-y-2">
                        @foreach ($this->recentActivity as $activity)
                            <div class="flex items-center gap-3 rounded-lg border p-3">
                                <div @class([
                                    'flex size-8 shrink-0 items-center justify-center rounded-full',
                                    'bg-success/10 text-success' => $activity->status === 'completed',
                                    'bg-info/10 text-info' => $activity->status === 'pending',
                                    'bg-destructive/10 text-destructive' => ! in_array($activity->status, ['completed', 'pending']),
                                ])>
                                    @if ($activity->status === 'completed')
                                        <x-ui.icon name="check" />
                                    @elseif ($activity->status === 'pending')
                                        <x-ui.spinner />
                                    @else
                                        <x-ui.icon name="x" />
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="line-clamp-1 text-sm font-medium">
                                        @if ($activity->job_url)
                                            <a href="{{ $activity->job_url }}" target="_blank" rel="noopener" class="hover:underline">{{ $activity->job_title ?? __('Job') }}</a>
                                        @else
                                            {{ $activity->job_title ?? __('Job') }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        @if ($activity->status === 'completed')
                                            {{ __('Scored :time', ['time' => $activity->updated_at->diffForHumans()]) }}
                                        @elseif ($activity->status === 'pending')
                                            {{ __('Calculating — started :time', ['time' => $activity->updated_at->diffForHumans()]) }}
                                        @else
                                            {{ __('Failed :time', ['time' => $activity->updated_at->diffForHumans()]) }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card-content>
        </x-ui.card>
    </div>
</div>
