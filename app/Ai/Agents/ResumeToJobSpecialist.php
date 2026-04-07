<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::OpenRouter)]
#[Model('deepseek/deepseek-v3.2')]
class ResumeToJobSpecialist implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return join(' ', [
            'You are a specialist in analyzing resumes and matching them to job descriptions.',
            'You will be given a resume and a job description, and you will return a rating of how well the resume matches the job description, on a scale from 0 to 10.',
            'You will also return a brief summary of the resume, highlighting the most relevant skills and experiences for the job description.',
            'The rating should be based on how well the skills and experiences listed in the resume match the requirements and preferences listed in the job description.',
            'The summary should focus on the most relevant skills and experiences for the job description, and should not simply repeat the contents of the resume. (max 300 characters)',
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

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'rating' => $schema->number()->description('The overall rating of how well the resume matches the job description, on a scale from 0 to 10.'),
            'summary' => $schema->string()->description('A brief summary of the resume, highlighting the most relevant skills and experiences for the job description.'),
        ];
    }
}
