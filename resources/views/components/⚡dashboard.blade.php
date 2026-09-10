<?php

use App\Models\JobRating;
use Livewire\Component;

new class extends Component
{
    public string $username = '';

    public ?string $cv = null;

    public ?array $keywords = null;

    public function mount()
    {
        $user = auth()->user();
        $this->username = $user->name;
        $this->cv = $user->cv;
        $this->keywords = $user->keywords;
    }

    public function getMatchStatsProperty(): array
    {
        $ratings = JobRating::where('user_id', auth()->id())->get();

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

    public function getTopJobsProperty()
    {
        return JobRating::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->whereNotNull('job_title')
            ->get()
            ->map(function ($rating) {
                $rating->score = $this->scoreFor($rating);

                return $rating;
            })
            ->sortByDesc('score')
            ->take(5)
            ->values();
    }

    public function getRecentActivityProperty()
    {
        return JobRating::where('user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();
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

    private function scoreBadge(float $score): string
    {
        return match (true) {
            $score >= 80 => 'badge-success',
            $score >= 50 => 'badge-warning',
            default => 'badge-error',
        };
    }
}
?>

<div class="py-10 mx-4">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    @auth
                    <div class="avatar pointer-events-none">
                        <div class="ring-primary ring-offset-base-100 w-12 rounded-full ring-2 ring-offset-2 pointer-events-none select-none">
                            <img src="{{ auth()->user()->avatar }}" />
                        </div>
                    </div>
                    @endauth
                    <span>Welcome back, <span class="text-primary">{{ $username }}</span>!</span>
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-300">Here's how your job search is going</p>
            </div>

            <div class="flex gap-2">
                @if ($cv)
                    <a href="{{ route('jobs') }}" wire:navigate class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Browse Jobs
                    </a>
                @else
                    <a href="{{ route('profile') }}" wire:navigate class="btn btn-warning gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Upload CV
                    </a>
                @endif
            </div>
        </div>

        @if (! $cv)
            <!-- Onboarding: nothing to show stats for yet -->
            <div class="card bg-base-100 shadow-lg mb-8">
                <div class="card-body">
                    <h2 class="text-xl font-bold mb-4">Get Started</h2>
                    <ul class="steps w-full">
                        <li class="step step-primary">Login</li>
                        <li class="step">Upload CV</li>
                        <li class="step">AI Analysis</li>
                        <li class="step">Find Jobs</li>
                    </ul>
                    <p class="text-sm text-gray-500 mt-4 text-center">
                        Upload your CV and our AI will analyze it, then start matching you with relevant jobs.
                    </p>
                    <div class="flex justify-center mt-2">
                        <a href="{{ route('profile') }}" wire:navigate class="btn btn-warning">Upload CV</a>
                    </div>
                </div>
            </div>
        @else
            <!-- Stat Tiles -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="card bg-base-100 shadow-lg border border-base-300">
                    <div class="card-body p-5">
                        <div class="text-sm font-medium text-base-content/60">Jobs Matched</div>
                        <div class="text-3xl font-bold text-primary">{{ $this->match_stats['completed'] }}</div>
                        <div class="text-xs text-base-content/50 mt-1">
                            @if ($this->match_stats['pending'] > 0)
                                {{ $this->match_stats['pending'] }} still calculating
                            @else
                                rated by AI so far
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-lg border border-base-300">
                    <div class="card-body p-5">
                        <div class="text-sm font-medium text-base-content/60">Average Match</div>
                        <div class="text-3xl font-bold {{ $this->scoreBadge($this->match_stats['average'] ?? 0) === 'badge-success' ? 'text-success' : ($this->scoreBadge($this->match_stats['average'] ?? 0) === 'badge-warning' ? 'text-warning' : 'text-error') }}">
                            {{ $this->match_stats['average'] !== null ? $this->match_stats['average'].'%' : '—' }}
                        </div>
                        <div class="text-xs text-base-content/50 mt-1">across all rated jobs</div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-lg border border-base-300">
                    <div class="card-body p-5">
                        <div class="text-sm font-medium text-base-content/60">Strong Matches</div>
                        <div class="text-3xl font-bold text-success">{{ $this->match_stats['strong'] }}</div>
                        <div class="text-xs text-base-content/50 mt-1">scoring 80% or above</div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-lg border border-base-300">
                    <div class="card-body p-5">
                        <div class="text-sm font-medium text-base-content/60">New This Week</div>
                        <div class="text-3xl font-bold text-info">{{ $this->match_stats['this_week'] }}</div>
                        <div class="text-xs text-base-content/50 mt-1">
                            {{ $this->match_stats['failed'] > 0 ? $this->match_stats['failed'].' failed rating'.($this->match_stats['failed'] > 1 ? 's' : '') : 'matched in the last 7 days' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Top 5 Best Scoring Jobs -->
                <div class="card bg-base-100 shadow-lg lg:col-span-2">
                    <div class="card-body">
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-xl font-bold">Top Matches</h2>
                            <a href="{{ route('jobs') }}" wire:navigate class="link link-primary link-sm">View all jobs →</a>
                        </div>

                        @if ($this->top_jobs->isEmpty())
                            <div class="text-center py-10 text-base-content/50">
                                <div class="text-4xl mb-3">🎯</div>
                                <p class="font-medium">No scored jobs yet</p>
                                <p class="text-sm mt-1">Use the AI Score button on the Jobs page, or enable auto-match in your profile, to get your first matches.</p>
                            </div>
                        @else
                            <div class="divide-y divide-base-200">
                                @foreach ($this->top_jobs as $i => $job)
                                    <div class="flex items-center gap-4 py-3">
                                        <div class="w-8 h-8 rounded-full {{ $i === 0 ? 'bg-warning/20 text-warning' : 'bg-base-200 text-base-content/60' }} flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ $i + 1 }}
                                        </div>
                                        <div class="grow min-w-0">
                                            <a href="{{ $job->job_url }}" target="_blank" class="font-semibold hover:text-primary transition-colors line-clamp-1">
                                                {{ $job->job_title }}
                                            </a>
                                            <div class="text-xs text-base-content/50 mt-0.5">Rated {{ $job->updated_at->diffForHumans() }}</div>
                                        </div>
                                        <div class="flex items-center gap-1 shrink-0 max-md:hidden">
                                            <div class="tooltip" data-tip="Skills: {{ number_format($job->skills_match ?? 0, 0) }}%">
                                                <div class="badge badge-ghost badge-sm">{{ number_format($job->skills_match ?? 0, 0) }}%</div>
                                            </div>
                                            <div class="tooltip" data-tip="Experience: {{ number_format($job->experience_relevance ?? 0, 0) }}%">
                                                <div class="badge badge-ghost badge-sm">{{ number_format($job->experience_relevance ?? 0, 0) }}%</div>
                                            </div>
                                            <div class="tooltip" data-tip="Seniority: {{ number_format($job->seniority_fit ?? 0, 0) }}%">
                                                <div class="badge badge-ghost badge-sm">{{ number_format($job->seniority_fit ?? 0, 0) }}%</div>
                                            </div>
                                            <div class="tooltip" data-tip="Keywords: {{ number_format($job->keyword_match ?? 0, 0) }}%">
                                                <div class="badge badge-ghost badge-sm">{{ number_format($job->keyword_match ?? 0, 0) }}%</div>
                                            </div>
                                        </div>
                                        <div class="shrink-0">
                                            <div class="badge {{ $this->scoreBadge($job->score) }} badge-lg gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                </svg>
                                                {{ number_format($job->score, 0) }}%
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card bg-base-100 shadow-lg">
                    <div class="card-body">
                        <h2 class="text-xl font-bold mb-2">Recent Activity</h2>

                        @if ($this->recent_activity->isEmpty())
                            <div class="text-center py-10 text-base-content/50">
                                <p class="text-sm">No activity yet. Once jobs are scored, they'll show up here.</p>
                            </div>
                        @else
                            <div class="space-y-2">
                                @foreach ($this->recent_activity as $activity)
                                    <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 {{ $activity->status === 'completed' ? 'bg-success/20' : ($activity->status === 'pending' ? 'bg-info/20' : 'bg-error/20') }}">
                                            @if ($activity->status === 'completed')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @elseif ($activity->status === 'pending')
                                                <span class="loading loading-spinner text-info w-4"></span>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-sm line-clamp-1">
                                                @if ($activity->job_url)
                                                    <a href="{{ $activity->job_url }}" target="_blank" class="hover:text-primary">{{ $activity->job_title ?? 'Job' }}</a>
                                                @else
                                                    {{ $activity->job_title ?? 'Job' }}
                                                @endif
                                            </p>
                                            <p class="text-xs text-base-content/50">
                                                @if ($activity->status === 'completed')
                                                    Scored {{ $activity->updated_at->diffForHumans() }}
                                                @elseif ($activity->status === 'pending')
                                                    Calculating — started {{ $activity->updated_at->diffForHumans() }}
                                                @else
                                                    Failed {{ $activity->updated_at->diffForHumans() }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>