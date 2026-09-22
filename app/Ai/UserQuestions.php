<?php

namespace App\Ai;

use Laravel\Ai\Contracts\Question;
use Laravel\Ai\Classification\Boolean;
use Laravel\Ai\Classification\Choice;
use Laravel\Ai\Classification\Score;

class UserQuestions
{
    /**
     * Build the AI question object for a single user-defined question.
     *
     * @param  array{key: string, type: string, question: string, options?: array<string, string>, levels?: list<string>}  $definition
     */
    public static function toAiQuestion(array $definition): Question
    {
        return match ($definition['type']) {
            'boolean' => new Boolean($definition['question']),
            'choice' => new Choice($definition['question'], $definition['options'] ?? []),
            'score' => new Score($definition['question'], array_values($definition['levels'] ?? [])),
        };
    }

    /**
     * Build the question objects for all definitions, keyed by question key.
     *
     * @param  array<int, array>  $definitions
     * @return array<string, Question>
     */
    public static function toAiQuestions(array $definitions): array
    {
        return collect($definitions)
            ->mapWithKeys(fn (array $definition) => [$definition['key'] => static::toAiQuestion($definition)])
            ->all();
    }
}