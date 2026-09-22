<?php

namespace App\Jobs;

use App\Ai\Agents\CoverLetterSpecialist;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateCoverLetter implements ShouldQueue
{
    use Queueable;

    /**
     * Must exceed the agent's HTTP timeout (300s) so the worker
     * doesn't kill the job while the model is still generating.
     */
    public $timeout = 330;

    /**
     * Create a new job instance.
     */
    public function __construct(public Post $post, public User $user) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $defaultCoverLetter = $this->user->defaultCoverLetter;
        
        if (!$defaultCoverLetter) {
            return;
        }

        $now = now()->format('Y-m-d');
        $newCoverLetter = $defaultCoverLetter->replicate();
        // Use the stored description when we have it; only fetch when missing.
        $description = $this->post->description ?? $this->post->fetchDescription();
        $jobDescription = trim(preg_replace('/\s+/', ' ', strip_tags($description ?? '')) ?? '');
        $letter = $defaultCoverLetter->content;
        $cv = json_encode($this->user->cv) ?? '';
        $skills = json_encode($this->user->skills) ?? '';

        // Change title to the job
        $newCoverLetter->title = $this->post->company_name . ' - ' . now()->format('Y-m-d H:i:s');
        $newCoverLetter->job_id = $this->post->id;

        $newCoverLetter->content = (new CoverLetterSpecialist)->prompt(<<<PROMPT
            Current date: $now

            ---Prompt---
            Please generate a tailored cover letter based on the job description and the current cover letter.
            The cover letter should be customized to highlight the applicant's relevant skills and experiences in relation to the job description.
            You may rewrite the cover letter to better match the job description while keeping the user's original content in mind.
            ---End Prompt---

            ---Cover Letter---
            $letter
            ---End Cover Letter---

            ---Job Description---
            $jobDescription
            ---End Job Description---

            ---CV---
            $cv
            ---End CV---

            ---Skills---
            Skills that the user possesses:
            $skills
            ---End Skills---
            PROMPT
        );
        
        $newCoverLetter->save();
    }
}
