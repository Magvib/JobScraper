<?php

namespace App\Jobs;

use App\Ai\Agents\ResumeToJobSpecialistPro;
use App\Mail\JobMatchNotification;
use App\Models\JobRating;
use App\Models\User;
use DOMDocument;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Ai\Files\Document;

class ProcessJobRating implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected JobRating $jobRating,
        protected User $user,
        protected bool $isCron = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $text = $this->fetchJobDescription($this->jobRating->job_url);

            if ($text === '') {
                throw new \Exception('Failed to fetch job description.');
            }
            
            $response = (new ResumeToJobSpecialistPro)->prompt(
                'Here is the job description: '.$text,
                attachments: [
                    Document::fromStorage($this->user->cv),
                ]
            );

            $updateData = [
                'skills_match' => $response->structured['skills_match'] ?? null,
                'skills_match_reasoning' => $response->structured['skills_match_reasoning'] ?? null,
                'experience_relevance' => $response->structured['experience_relevance'] ?? null,
                'experience_relevance_reasoning' => $response->structured['experience_relevance_reasoning'] ?? null,
                'seniority_fit' => $response->structured['seniority_fit'] ?? null,
                'seniority_fit_reasoning' => $response->structured['seniority_fit_reasoning'] ?? null,
                'keyword_match' => $response->structured['keyword_match'] ?? null,
                'keyword_match_reasoning' => $response->structured['keyword_match_reasoning'] ?? null,
                'status' => 'completed',
            ];

            $this->jobRating->update($updateData);

            if ($this->isCron && $this->shouldNotify($this->jobRating, $this->user)) {
                Mail::to($this->user)->queue(new JobMatchNotification($this->jobRating, $this->user));
            }
        } catch (\Throwable $th) {
            Log::error("Failed to process job rating for JobRating ID {$this->jobRating->id}: ".$th->getMessage());

            $this->jobRating->update([
                'status' => 'failed',
            ]);
        }
    }

    private function fetchJobDescription(string $jobUrl): string
    {
        return Cache::remember('job_description_'.md5($jobUrl), now()->addHours(6), function () use ($jobUrl) {
            $body = $this->fetchJobBody($jobUrl);

            if ($body === '') {
                return '';
            }

            $dom = new DOMDocument();
            @$dom->loadHTML($body);

            $scriptTags = $dom->getElementsByTagName('script');
            for ($i = $scriptTags->length - 1; $i >= 0; $i--) {
                $scriptTags->item($i)->parentNode->removeChild($scriptTags->item($i));
            }

            $styleTags = $dom->getElementsByTagName('style');
            for ($i = $styleTags->length - 1; $i >= 0; $i--) {
                $styleTags->item($i)->parentNode->removeChild($styleTags->item($i));
            }

            $text = $dom->textContent ?? '';
            $text = preg_replace('/\s+/', ' ', $text) ?? '';

            return trim($text);
        });
    }

    private function fetchJobBody(string $jobUrl): string
    {
        try {
            $response = Http::retry(2, 200)
                ->connectTimeout(5)
                ->timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                ])
                ->get($jobUrl);

            if (! $response->successful()) {
                Log::warning('Auto-match job fetch failed.', [
                    'job_url' => $jobUrl,
                    'status' => $response->status(),
                ]);

                return '';
            }

            return $response->body();
        } catch (\Throwable $th) {
            Log::warning('Auto-match job fetch exception.', [
                'job_url' => $jobUrl,
                'error' => $th->getMessage(),
            ]);

            return '';
        }
    }

    private function shouldNotify(JobRating $jobRating, User $user): bool
    {
        $thresholds = [
            'skills_match' => $user->notify_skills_match_threshold,
            'experience_relevance' => $user->notify_experience_relevance_threshold,
            'seniority_fit' => $user->notify_seniority_fit_threshold,
            'keyword_match' => $user->notify_keyword_match_threshold,
        ];

        $checks = [];

        foreach ($thresholds as $attribute => $threshold) {
            if ($threshold === null) {
                continue;
            }

            $value = $jobRating->{$attribute};

            if ($value === null) {
                $checks[] = false;

                continue;
            }

            $checks[] = $value >= $threshold;
        }

        if ($checks === []) {
            return false;
        }

        $mode = $user->notify_match_mode ?? 'any';

        if ($mode === 'all') {
            return ! in_array(false, $checks, true);
        }

        return in_array(true, $checks, true);
    }
}
