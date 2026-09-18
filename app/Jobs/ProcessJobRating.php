<?php

namespace App\Jobs;

use App\Ai\Agents\ResumeToJobSpecialistPro;
use App\Mail\JobMatchNotification;
use App\Models\JobRating;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessJobRating implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Post $post,
        protected User $user,
        protected bool $isCron = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $text = trim(preg_replace('/\s+/', ' ', strip_tags($this->post->fetchDescription() ?? '')) ?? '');

            if ($text === '') {
                throw new \Exception('Failed to fetch job description.');
            }

            $response = (new ResumeToJobSpecialistPro)->prompt(
                "Current date: ". now() . "\n" .
                "Here is the job description: ". $text . "\n" .
                "And here is the users CV in json: " . json_encode($this->user->cv_json) . "\n" .
                "And here is his skills in json: " . json_encode($this->user->skills),
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

            $this->post->ratingFor($this->user)->update($updateData);

            if ($this->isCron && ($rating = $this->post->ratingFor($this->user)->first()) && $this->shouldNotify($rating, $this->user)) {
                Mail::to($this->user)->queue(new JobMatchNotification($rating, $this->user));
            }
        } catch (\Throwable $th) {
            Log::error("Failed to process job rating for Post ID {$this->post->id}: ".$th->getMessage());

            $this->post->ratingFor($this->user)->update([
                'status' => 'failed',
            ]);
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