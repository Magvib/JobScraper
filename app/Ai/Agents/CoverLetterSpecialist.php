<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::OpenRouter), Model('z-ai/glm-5.3-flash')]
class CoverLetterSpecialist implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return join(' ', [
            'You are a specialist in analyzing cover letters.',
            'If the user has selected text in the cover letter, focus your task on that section.',
            'Do not provide any feedback because what you return is directly reflected in the cover letter.',
            'If you do not understand the question or the context, Just return the original text.',
            'Always ensure that your output is concise and relevant to the selected text or the entire cover letter.'
        ]);
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }
}
