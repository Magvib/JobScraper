<?php

namespace App\Jobs;

use App\Ai\UserQuestions;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Classification;

class ProcessPostQuestions implements ShouldQueue
{
    use Queueable;

    public int $timeout = 330;

    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Post $post,
        protected User $user,
    ) {}

    /**
     * Answer the user's custom questions about the post using AI classification.
     */
    public function handle(): void
    {
        try {
            $definitions = $this->user->questions ?? [];

            if ($definitions === []) {
                $this->post->questionAnswersFor($this->user)->update([
                    'status' => 'failed',
                ]);

                return;
            }

            $text = trim(preg_replace('/\s+/', ' ', strip_tags($this->post->fetchDescription() ?? '')) ?? '');

            if ($text === '') {
                throw new \Exception('Failed to fetch job description.');
            }

            $response = Classification::of([
                'title' => $this->post->title,
                'company' => $this->post->company_name,
                'location' => $this->post->getLocation(),
                'description' => Str::limit($text, 20000),
            ])
                ->questions(UserQuestions::toAiQuestions($definitions))
                ->timeout(300)
                ->classify();

            $answers = [];

            foreach ($definitions as $definition) {
                $answers[$definition['key']] = [
                    'type' => $definition['type'],
                    'answer' => $response->answer($definition['key'])->toArray(),
                ];
            }

            $this->post->questionAnswersFor($this->user)->update([
                'answers' => $answers,
                'status' => 'completed',
            ]);
        } catch (\Throwable $th) {
            Log::error("Failed to process post questions for Post ID {$this->post->id}: ".$th->getMessage());

            $this->post->questionAnswersFor($this->user)->update([
                'status' => 'failed',
            ]);
        }
    }
}