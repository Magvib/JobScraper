<?php

use App\Jobs\ProcessJobRating;
use App\Models\JobRating;
use App\Models\Post;
use App\Models\PostSource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

new class extends Component
{
    // https://www.jobindex.dk/api/jobsearch/v3/jobcount?subid=1&radius=60&address=Svinglen+24%2C+8800+Viborg&q=php
    // https://www.jobindex.dk/api/jobsearch/v3?q=php&radius=60&address=Svinglen+24%2C+8800+Viborg
    // https://jobnet.dk/bff/FindJob/Search?resultsPerPage=20&pageNumber=1&orderType=BestMatch&searchString=php

    public $jobs;

    public string $search = '';

    public string $sort = 'date';

    public function mount()
    {
        $user = auth()->user();

        if ($user->keywords) {
            $data = [
                'q' => implode(' ', $user->keywords),
                'sort' => 'date',
            ];

            if ($user->address && $user->max_distance) {
                $data['address'] = $user->address.', '.$user->zip.' '.$user->city;
                $data['radius'] = $user->max_distance;
            }

            $cacheKey = 'jobindex_'.md5(json_encode($data));

            $jobs = Cache::remember($cacheKey.'_results', now()->addMinutes(30), function () use ($data) {
                return Http::get('https://www.jobindex.dk/api/jobsearch/v3', $data)->json()['results'] ?? [];
            });

            foreach ($jobs as $job) {
                Post::savePost($job, PostSource::JOBINDEX);
            }

            // Jobnet only supports one search string per request, so one request per keyword.
            $jobnetKey = 'jobnet_'.md5(json_encode($user->keywords));

            $jobnet = Cache::remember($jobnetKey, now()->addMinutes(30), function () use ($user) {
                $ads = collect();
                $count = 0;

                $keywords = collect($user->keywords)->take(5);

                foreach ($keywords as $keyword) {
                    try {
                        $response = Http::retry(2, 200)
                            ->connectTimeout(5)
                            ->timeout(15)
                            ->withHeaders([
                                'x-csrf' => 1,
                            ])
                            ->get('https://jobnet.dk/bff/FindJob/Search', [
                                'resultsPerPage' => 20,
                                'pageNumber' => 1,
                                'orderType' => 'BestMatch',
                                'searchString' => $keyword,
                            ])->json() ?? [];
                    } catch (\Throwable $th) {
                        continue;
                    }

                    $count += $response['totalJobAdsCount'] ?? count($response['jobAds'] ?? []);
                    $ads = $ads->merge($response['jobAds'] ?? []);
                }

                return ['count' => $count, 'jobs' => $ads->all()];
            });

            foreach ($jobnet['jobs'] as $job) {
                Post::savePost($job, PostSource::JOBNET);
            }

            $this->jobs = Post::query()->active()->get();
        }
    }

    public function with()
    {
    }

    public function companyInitials(string $name): string
    {
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    public function formatDate(string $date): string
    {
        return Carbon::parse($date)->diffForHumans();
    }

    public function formatDistance(float $distance): string
    {
        return round($distance).' '.__('km');
    }

    public function aiScore($postId)
    {
        $post = Post::find($postId);
        $user = auth()->user();
        
        if (!$post) {
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
};
?>

<div class="py-10 mx-4">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Job Listings') }}</h1>
                <div class="flex items-center gap-2">
                    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ number_format(count($jobs)) }} {{ __('jobs found') }}</p>
                    <a class="btn btn-xs mt-2" href="{{ route('profile') }}" wire:navigate>
                        {{ __('Change keywords or location') }}
                    </a>
                </div>
            </div>
            <div class="badge badge-primary badge-outline">
                <span class="w-2 h-2 bg-primary rounded-full animate-pulse mr-2"></span>
                {{ __('Live results') }}
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 16v-4"/>
                            <path d="M12 8h.01"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-base-content">{{ __('AI Ratings Guide') }}</p>
                        <p class="text-sm text-base-content/60">{{ __('Scores compare your CV with each job posting. Click "AI Score" on a job to start the analysis. Tooltips on the badges explain the reasoning.') }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="rounded-box bg-base-200/50 p-3 text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current text-success" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        <div class="grow">
                            <div class="font-medium">{{ __('Skills Match') }}</div>
                            <div class="text-base-content/60">{{ __('80%+ = strong alignment') }}</div>
                        </div>
                        <span class="badge badge-success badge-xs">{{ __('80%+') }}</span>
                    </div>
                    <div class="rounded-box bg-base-200/50 p-3 text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current text-warning" viewBox="0 0 24 24">
                            <path d="M20 7h-4V4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v3H4c-1.2 0-2 .8-2 2v11c0 1.2.8 2 2 2h16c1.2 0 2-.8 2-2V9c0-1.2-.8-2-2-2zM10 4h4v3h-4V4z"/>
                        </svg>
                        <div class="grow">
                            <div class="font-medium">{{ __('Experience Relevance') }}</div>
                            <div class="text-base-content/60">{{ __('50-79% = partial match') }}</div>
                        </div>
                        <span class="badge badge-warning badge-xs">{{ __('50-79%') }}</span>
                    </div>
                    <div class="rounded-box bg-base-200/50 p-3 text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current text-error" viewBox="0 0 24 24">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                        </svg>
                        <div class="grow">
                            <div class="font-medium">{{ __('Seniority Fit') }}</div>
                            <div class="text-base-content/60">{{ __('Under 50% = mismatch') }}</div>
                        </div>
                        <span class="badge badge-error badge-xs">{{ __('<50%') }}</span>
                    </div>
                    <div class="rounded-box bg-base-200/50 p-3 text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current text-info" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/>
                        </svg>
                        <div class="grow">
                            <div class="font-medium">{{ __('Keyword Match (ATS)') }}</div>
                            <div class="text-base-content/60">{{ __('Checks required terms') }}</div>
                        </div>
                        <span class="badge badge-info badge-xs">{{ __('ATS') }}</span>
                    </div>
                </div>
                <div class="text-xs text-base-content/60">
                    {{ __('New scores appear as "Calculating..." and update automatically. Re-run "AI Score" if you update your CV or keywords.') }}
                </div>
            </div>
        </div>

        @if(count($jobs) === 0)
            <div class="card bg-base-100 shadow-sm mt-4">
                <div class="card-body items-center text-center py-16">
                    <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-base-content/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.3-4.3"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold">{{ __('No jobs found') }}</h3>
                    <p class="text-base-content/60">{{ __('Try updating your search keywords in your profile.') }}</p>
                    <a href="{{ route('profile') }}" class="btn btn-primary mt-4">{{ __('Update Profile') }}</a>
                </div>
            </div>
        @else
            <div class="card bg-base-100 shadow-sm mb-4">
                <div class="card-body p-4 flex flex-col sm:flex-row gap-3 sm:items-center">
                    <label class="input input-sm flex items-center gap-2 grow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-base-content/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.3-4.3"/>
                        </svg>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="{{ __('Search title, company or location...') }}"
                            class="grow"
                        />
                    </label>
                    <div class="join">
                        @foreach(['date' => __('Date'), 'az' => __('A-Z'), 'distance' => __('Distance'), 'score' => __('AI score')] as $sortKey => $sortLabel)
                            <button
                                class="btn btn-sm join-item {{ $sort === $sortKey ? 'btn-primary' : 'btn-ghost' }}"
                                wire:click="$set('sort', '{{ $sortKey }}')"
                            >
                                {{ $sortLabel }}
                            </button>
                        @endforeach
                    </div>
                    @if(trim($search) !== '')
                        <span class="text-xs text-base-content/60 whitespace-nowrap">
                            {{ number_format(count($jobs)) }} / {{ number_format(count($jobs)) }} {{ __('shown') }}
                        </span>
                    @endif
                </div>
            </div>

            @if(empty($jobs))
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body items-center text-center py-10">
                        <p class="text-base-content/60">{{ __('No jobs match your search or filters.') }}</p>
                    </div>
                </div>
            @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($jobs as $job)
                    <div class="card bg-base-100 border border-base-300 hover:border-primary/50 transition-colors duration-300">
                        <div class="card-body p-5">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center text-primary font-bold text-sm shrink-0">
                                    {{ $this->companyInitials($job->company_name) }}
                                </div>
                                <div class="grow min-w-0">
                                    <a href="{{ $job->canonical_url }}" target="_blank" class="font-semibold text-base hover:text-primary transition-colors line-clamp-2">
                                        {{ $job->title }}
                                    </a>
                                    <div class="text-sm text-base-content/60 mt-1">{{ $job->company_name }}</div>
                                </div>
                                @if ($job->rating?->status === 'pending')
                                    <div class="badge badge-info badge-sm" wire:poll.5000ms>
                                        {{ __('Calculating...') }}
                                    </div>
                                @elseif ($job->rating?->status === 'failed')
                                    <div class="badge badge-error badge-sm">
                                        {{ __('Failed') }}
                                    </div>
                                @elseif($job->rating?->status === 'completed')
                                    <div class="flex items-center gap-1 shrink-0">
                                        <div class="tooltip tooltip-info" data-tip="{{ $job->rating?->skills_match_reasoning }}">
                                            <div class="badge {{ $job->rating?->skills_match >= 80 ? 'badge-success' : ($job->rating?->skills_match >= 50 ? 'badge-warning' : 'badge-error') }} badge-sm gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                </svg>
                                                {{ number_format($job->rating?->skills_match, 0) }}%
                                            </div>
                                        </div>
                                        <div class="tooltip tooltip-info" data-tip="{{ $job->rating?->experience_relevance_reasoning }}">
                                            <div class="badge {{ $job->rating?->experience_relevance >= 80 ? 'badge-success' : ($job->rating?->experience_relevance >= 50 ? 'badge-warning' : 'badge-error') }} badge-sm gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                                    <path d="M20 7h-4V4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v3H4c-1.2 0-2 .8-2 2v11c0 1.2.8 2 2 2h16c1.2 0 2-.8 2-2V9c0-1.2-.8-2-2-2zM10 4h4v3h-4V4z"/>
                                                </svg>
                                                {{ number_format($job->rating?->experience_relevance, 0) }}%
                                            </div>
                                        </div>
                                        <div class="tooltip tooltip-info" data-tip="{{ $job->rating?->seniority_fit_reasoning }}">
                                            <div class="badge {{ $job->rating?->seniority_fit >= 80 ? 'badge-success' : ($job->rating?->seniority_fit >= 50 ? 'badge-warning' : 'badge-error') }} badge-sm gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                                </svg>
                                                {{ number_format($job->rating?->seniority_fit, 0) }}%
                                            </div>
                                        </div>
                                        <div class="tooltip tooltip-info" data-tip="{{ $job->rating?->keyword_match_reasoning }}">
                                            <div class="badge {{ $job->rating?->keyword_match >= 80 ? 'badge-success' : ($job->rating?->keyword_match >= 50 ? 'badge-warning' : 'badge-error') }} badge-sm gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm0-14c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm0-6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                                </svg>
                                                {{ number_format($job->rating?->keyword_match, 0) }}%
                                            </div>
                                        </div>
                                        <div class="divider divider-horizontal mx-0.5"></div>
                                        <div class="tooltip" data-tip="{{ __('Average of the scores above') }}">
                                            <div class="badge {{ $job['avg_score'] >= 80 ? 'badge-success' : ($job['avg_score'] >= 50 ? 'badge-warning' : 'badge-error') }} badge-sm badge-outline gap-1">
                                                {{ __('Avg') }} {{ number_format($job['avg_score'], 0) }}%
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-3 mt-4 text-sm">
                                <span class="badge badge-ghost badge-sm">{{ $job['source'] ?? 'Jobindex' }}</span>
                                <div class="flex items-center gap-1.5 text-base-content/60">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    {{ $job->getLocation() }}
                                    @if(false)
                                        <span class="text-base-content/40">({{ $distance }})</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 text-base-content/60">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    {{ $job->published_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="card-actions justify-end mt-4">
                                <button class="btn btn-secondary btn-sm gap-2" wire:click="aiScore('{{ $job->id }}')">
                                    {{ __('AI Score') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                </button>
                                <a href="{{ $job->canonical_url }}" target="_blank" class="btn btn-primary btn-sm gap-2">
                                    {{ __('View Job') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                        <polyline points="15 3 21 3 21 9"/>
                                        <line x1="10" y1="14" x2="21" y2="3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        @endif
    </div>
</div>
