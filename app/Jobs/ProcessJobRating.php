<?php

namespace App\Jobs;

use App\Ai\Agents\ResumeToJobSpecialistPro;
use App\Models\JobRating;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
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
        protected string $text,
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $response = (new ResumeToJobSpecialistPro)->prompt(
                "Here is the job description: " . $this->text,
                attachments: [
                    Document::fromStorage($this->user->cv),
                ]
            );
            
            $this->jobRating->update([
                'skills_match' => $response->structured['skills_match'] ?? null,
                'skills_match_reasoning' => $response->structured['skills_match_reasoning'] ?? null,
                'experience_relevance' => $response->structured['experience_relevance'] ?? null,
                'experience_relevance_reasoning' => $response->structured['experience_relevance_reasoning'] ?? null,
                'seniority_fit' => $response->structured['seniority_fit'] ?? null,
                'seniority_fit_reasoning' => $response->structured['seniority_fit_reasoning'] ?? null,
                'keyword_match' => $response->structured['keyword_match'] ?? null,
                'keyword_match_reasoning' => $response->structured['keyword_match_reasoning'] ?? null,
                'status' => 'completed',
            ]);
        } catch (\Throwable $th) {
            Log::error("Failed to process job rating for JobRating ID {$this->jobRating->id}: " . $th->getMessage());
            
            $this->jobRating->update([
                'status' => 'failed'
            ]);
        }
    }
}
