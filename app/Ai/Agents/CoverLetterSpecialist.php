<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::OpenRouter), Model('z-ai/glm-5.3-flash'), Timeout(300)]
class CoverLetterSpecialist implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return join(' ', [
            'You are a specialist in analyzing and rewriting cover letters.',
            'If the user has ---Selected Text--- in the prompt, ONLY CHANGE THAT SECTION. and keep the rest unchanged so you still have to print out the entire cover letter but only change that section that the user selected.',
            'If the ---Selected Text--- section is empty or not present, just ignore the instruction regarding it.',
            'Do not provide any feedback because what you return is directly reflected in the cover letter.',
            'If you do not understand the question or the context, Just return the original text.',
            'Do not use – or — otherwise the company might think that the cover letter is written by someone else, use commas instead.',
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
