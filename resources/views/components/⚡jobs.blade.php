<?php

use App\Jobs\ProcessJobRating;
use App\Models\JobRating;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

new class extends Component
{
    // https://www.jobindex.dk/api/jobsearch/v3/jobcount?subid=1&radius=60&address=Svinglen+24%2C+8800+Viborg&q=php
    // https://www.jobindex.dk/api/jobsearch/v3?q=php&radius=60&address=Svinglen+24%2C+8800+Viborg
    
    public int $jobCount = 0;

    public array $jobs = [];

    public array $ratings = [];

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

            $this->jobCount = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($data) {
                return Http::get('https://www.jobindex.dk/api/jobsearch/v3/jobcount', $data)->json()['hitcount'] ?? 0;
            });

            $this->jobs = Cache::remember($cacheKey.'_results', now()->addMinutes(30), function () use ($data) {
                return Http::get('https://www.jobindex.dk/api/jobsearch/v3', $data)->json()['results'] ?? [];
            });

            $this->getRatings();
        }
    }

    public function getRatings()
    {
        $user = auth()->user();
        $this->ratings = JobRating::where('user_id', $user->id)->whereIn('job_id', collect($this->jobs)->pluck('tid'))->get()->keyBy('job_id')->toArray();
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
        return round($distance).' km';
    }

    public function aiScore($jobId)
    {
        $user = auth()->user();
        $jobRating = JobRating::where('user_id', $user->id)->where('job_id', $jobId)->first();

        if ($jobRating) {
            // if ($jobRating->rating >= 1) {
            //     $this->dispatch('toast', 
            //         message: 'You have already calculated the AI score for this job.',
            //         type: 'error'
            //     );
            //     return;
            // } else {
            //     $jobRating->delete();
            // }
            $jobRating->delete();
        }
        
        $job = collect($this->jobs)->firstWhere('tid', $jobId);
        
        if (!$job) {
            $this->dispatch('toast', 
                message: 'Job not found.',
                type: 'error'
            );
            return;
        }

        $jobUrl = $job['url'] ?? null;

        if (!$jobUrl) {
            $this->dispatch('toast', 
                message: 'Job URL not found.',
                type: 'error'
            );
            return;
        }

        $text = Cache::remember('job_description_'.md5($jobUrl), now()->addHours(6), function () use ($jobUrl) {
            $body = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            ])->get($jobUrl)->body();
            
            $dom = new DOMDocument();
            @$dom->loadHTML($body);

            // Remove script and style tags
            $scriptTags = $dom->getElementsByTagName('script');
            for ($i = $scriptTags->length - 1; $i >= 0; $i--) {
                $scriptTags->item($i)->parentNode->removeChild($scriptTags->item($i));
            }
            $styleTags = $dom->getElementsByTagName('style');
            for ($i = $styleTags->length - 1; $i >= 0; $i--) {
                $styleTags->item($i)->parentNode->removeChild($styleTags->item($i));
            }
            
            $text = $dom->textContent;
            $text = preg_replace('/\s+/', ' ', $text); // Replace multiple whitespace with single space
            $text = trim($text);
            
            return $text;
        });

        $user = auth()->user();

        if (Storage::missing($user->cv)) {
            $this->dispatch('toast', 
                message: 'CV not found.',
                type: 'error'
            );
            return;
        }

        $jobRating = JobRating::create([
            'user_id' => $user->id,
            'job_id' => $jobId,
        ]);

        ProcessJobRating::dispatch($jobRating, $user, $text);

        $this->getRatings();

        $this->dispatch('toast', 
            message: 'AI score is being calculated. Please check back in a few moments.',
            type: 'success'
        );
    }
};
?>

<div class="py-10">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Job Listings</h1>
                <div class="flex items-center gap-2">
                    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ number_format($jobCount) }} jobs found</p>
                    <a class="btn btn-xs mt-2" href="{{ route('profile') }}" wire:navigate>
                        Change keywords or location
                    </a>
                </div>
            </div>
            <div class="badge badge-primary badge-outline">
                <span class="w-2 h-2 bg-primary rounded-full animate-pulse mr-2"></span>
                Live results
            </div>
        </div>

        @if(empty($jobs))
            <div class="card bg-base-100 shadow-sm mt-4">
                <div class="card-body items-center text-center py-16">
                    <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-base-content/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.3-4.3"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold">No jobs found</h3>
                    <p class="text-base-content/60">Try updating your search keywords in your profile.</p>
                    <a href="{{ route('profile') }}" class="btn btn-primary mt-4">Update Profile</a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($jobs as $job)
                    @php
                        $companyName = $job['company']['name'] ?? 'Unknown Company';
                        $location = $job['area'] ?? 'Remote';
                        $postedDate = $this->formatDate($job['firstdate'] ?? date('Y-m-d'));
                        $distance = isset($job['distance']) ? $this->formatDistance($job['distance']) : null;
                        // $rating = $job['rating']['score'] ?? null;
                        $jobUrl = $job['url'] ?? '#';
                        $headline = $job['headline'] ?? 'No title';
                        $jobId = $job['tid'] ?? null;

                        # Ratings
                        $ratingStatus = $this->ratings[$job['tid']]['status'] ?? null;
                        $skillsMatch = $this->ratings[$job['tid']]['skills_match'] ?? null;
                        $skillsMatchDesc = $this->ratings[$job['tid']]['skills_match_reasoning'] ?? '';
                        $experienceRelevance = $this->ratings[$job['tid']]['experience_relevance'] ?? null;
                        $experienceRelevanceDesc = $this->ratings[$job['tid']]['experience_relevance_reasoning'] ?? '';
                        $seniorityFit = $this->ratings[$job['tid']]['seniority_fit'] ?? null;
                        $seniorityFitDesc = $this->ratings[$job['tid']]['seniority_fit_reasoning'] ?? '';
                        $keywordMatch = $this->ratings[$job['tid']]['keyword_match'] ?? null;
                        $keywordMatchDesc = $this->ratings[$job['tid']]['keyword_match_reasoning'] ?? '';
                    @endphp
                    <div class="card bg-base-100 border border-base-300 hover:border-primary/50 transition-colors duration-300">
                        <div class="card-body p-5">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center text-primary font-bold text-sm shrink-0">
                                    {{ $this->companyInitials($companyName) }}
                                </div>
                                <div class="grow min-w-0">
                                    <a href="{{ $jobUrl }}" target="_blank" class="font-semibold text-base hover:text-primary transition-colors line-clamp-2">
                                        {{ $headline }}
                                    </a>
                                    <div class="text-sm text-base-content/60 mt-1">{{ $companyName }}</div>
                                </div>
                                @if ($ratingStatus === 'pending')
                                    <div class="badge badge-info badge-sm">
                                        Calculating...
                                    </div>
                                @elseif ($ratingStatus === 'failed')
                                    <div class="badge badge-error badge-sm">
                                        Failed
                                    </div>
                                @elseif($ratingStatus === 'completed')
                                    <div class="tooltip tooltip-info gap-1 shrink-0" data-tip="{{ $skillsMatchDesc }}">
                                        <div class="badge {{ $skillsMatch >= 80 ? 'badge-success' : ($skillsMatch >= 50 ? 'badge-warning' : 'badge-error') }} badge-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                            {{ number_format($skillsMatch, 0) }}%
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-3 mt-4 text-sm">
                                <div class="flex items-center gap-1.5 text-base-content/60">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    {{ $location }}
                                    @if($distance)
                                        <span class="text-base-content/40">({{ $distance }})</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 text-base-content/60">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    {{ $postedDate }}
                                </div>
                            </div>
                            <div class="card-actions justify-end mt-4">
                                <button class="btn btn-secondary btn-sm gap-2" wire:click="aiScore('{{ $jobId }}')">
                                    AI Score
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                </button>
                                <a href="{{ $jobUrl }}" target="_blank" class="btn btn-primary btn-sm gap-2">
                                    View Job
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
    </div>
</div>