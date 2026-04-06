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
class KeywordSpecialist implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $limit = 10;
        
        return join(' ', [
            'You are a specialist in keyword research.',
            'You will be given a resume and you will return the most relevant keywords that can be used to find this resume.',
            'And keywords can only be one word fx (Java, PHP, Laravel, Electrician, Plumber, Carpenter).',
            'Keep the keywords as specific as possible and avoid returning generic keywords like "programming", "development", "construction".',
            'That way the keywords are more effective when used for searching.',
            'The keywords are used to find new jobs for the person whose resume you are analyzing, so try to find keywords that are relevant for the jobs they are looking for.',
            'Keywords should not be related to the previous job names or locations, but rather to the skills and qualifications of the person.',
            'Keywords should not be related generic terms like "programming", "development", "construction".',
            'Return at most ' . $limit . ' keywords.',
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
            'keywords' => $schema->array()->items(
                $schema->string()
            )->required(),
        ];
    }
}
