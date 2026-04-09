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

            $jobs = array_slice($this->fetchJobsForUser($user), 0, 20);

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

                return [];
            }

            $results = $response->json()['results'] ?? [];

            return is_array($results) ? $results : [];
        } catch (\Throwable $th) {
            Log::warning('Auto-match job search exception.', [
                'user_id' => $user->id,
                'error' => $th->getMessage(),
            ]);

            return [];
        }
    }
}
