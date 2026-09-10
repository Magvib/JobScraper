<?php

namespace App\Console\Commands;

use App\Jobs\ProcessJobRating;
use App\Models\JobRating;
use App\Models\User;
use DOMDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AutoMatchNewJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:auto-match';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically rate the newest jobs for users who enabled auto-matching.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $users = User::query()
            ->where('auto_match_new_jobs', true)
            ->whereNotNull('keywords')
            ->whereNotNull('cv')
            ->cursor();

        foreach ($users as $user) {
            if (empty($user->keywords)) {
                continue;
            }

            if (Storage::missing($user->cv)) {
                Log::warning('Auto-match skipped due to missing CV.', [
                    'user_id' => $user->id,
                    'cv_path' => $user->cv,
                ]);

                continue;
            }

            $jobs = collect($this->fetchJobsForUser($user))
                ->unique(fn ($job) => $job['url'] ? rtrim(strtolower($job['url']), '/') : 'tid:'.$job['tid'])
                ->sortByDesc(fn ($job) => $job['firstdate'] ?? '')
                ->values()
                ->slice(0, 20)
                ->all();

            if ($jobs === []) {
                continue;
            }

            $jobIds = collect($jobs)
                ->pluck('tid')
                ->filter()
                ->values()
                ->all();

            if ($jobIds === []) {
                continue;
            }

            $existingJobIds = JobRating::where('user_id', $user->id)
                ->whereIn('job_id', $jobIds)
                ->pluck('job_id')
                ->all();

            $existingJobIds = array_fill_keys($existingJobIds, true);

            foreach ($jobs as $job) {
                $jobId = $job['tid'] ?? null;

                if (! $jobId || isset($existingJobIds[$jobId])) {
                    continue;
                }

                $jobTitle = $job['headline'] ?? null;
                $jobUrl = $job['url'] ?? null;

                if (! $jobUrl) {
                    continue;
                }

                $jobRating = JobRating::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'job_id' => $jobId,
                    ],
                    [
                        'job_title' => $jobTitle,
                        'job_url' => $jobUrl,
                    ],
                );

                if (! $jobRating->wasRecentlyCreated) {
                    continue;
                }

                ProcessJobRating::dispatch($jobRating, $user, true);
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchJobsForUser(User $user): array
    {
        $data = [
            'q' => implode(' ', $user->keywords),
            'sort' => 'date',
        ];

        if ($user->address && $user->max_distance) {
            $data['address'] = $user->address.', '.$user->zip.' '.$user->city;
            $data['radius'] = $user->max_distance;
        }

        $jobs = [];

        try {
            $response = Http::retry(2, 200)
                ->connectTimeout(5)
                ->timeout(15)
                ->get('https://www.jobindex.dk/api/jobsearch/v3', $data);

            if (! $response->successful()) {
                Log::warning('Auto-match job search failed.', [
                    'user_id' => $user->id,
                    'status' => $response->status(),
                ]);
            } else {
                $results = $response->json()['results'] ?? [];

                $jobs = collect(is_array($results) ? $results : [])
                    ->map(fn ($job) => $job + ['source' => 'Jobindex'])
                    ->all();
            }
        } catch (\Throwable $th) {
            Log::warning('Auto-match job search exception.', [
                'user_id' => $user->id,
                'error' => $th->getMessage(),
            ]);
        }

        // Jobnet only supports one search string per request, so one request per keyword.
        foreach (collect($user->keywords)->take(5) as $keyword) {
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

            $ads = collect($response['jobAds'] ?? [])
                ->map(fn ($ad) => $this->normalizeJobnetAd($ad));

            $jobs = [...$jobs, ...$ads->all()];
        }

        return $jobs;
    }

    private function normalizeJobnetAd(array $ad): array
    {
        return [
            'tid' => $ad['jobAdId'] ?? null,
            'headline' => $ad['title'] ?? null,
            'url' => $ad['jobAdUrl'] ?: "https://jobnet.dk/find-job/" . ($ad['jobAdId'] ?: ''),
            'companytext' => $ad['hiringOrgName'] ?? null,
            'area' => $ad['municipality'] ?? $ad['postalDistrictName'] ?? $ad['country'] ?? null,
            'firstdate' => $ad['publicationDate'] ?? null,
            'source' => 'Jobnet',
        ];
    }
}
