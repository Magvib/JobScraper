<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Timeout;
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
#[Model('z-ai/glm-5.3-flash')]
#[Timeout(200)]
class ResumeToJobSpecialistPro implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $currentDate = now()->toDateString();
        $currentTime = now()->toTimeString();

        return <<<TEXT
        You are an expert recruiter and resume screening specialist.

        If you need the current date or time to answer, use the following values:
        Current date: {$currentDate}
        Current time: {$currentTime}

        Your task is to evaluate how well a candidate's resume matches a given job description.

        You MUST return structured scores from 0 to 100 for each category, where:
        - 0 = no match at all
        - 50 = moderate / partial match
        - 100 = excellent / near perfect match

        Be consistent, strict, and realistic. Do NOT inflate scores.

        SCORING RULES:

        1. Skills Match:
        - Compare candidate skills with required and preferred job skills
        - Required skills are more important than preferred
        - Missing critical required skills should significantly reduce the score

        1.5 Skills Match Reasoning:
        - Provide a brief explanation for the skills match score, highlighting key matches and critical missing skills. Max 300 characters.

        2. Experience Relevance:
        - Evaluate similarity between past responsibilities and the job description
        - Consider role titles, tasks, and domain relevance
        - Irrelevant experience should lower the score even if skills exist

        2.5 Experience Relevance Reasoning:
        - Provide a brief explanation for the experience relevance score, noting specific relevant roles and any irrelevant experience. Max 300 characters.

        3. Seniority Fit:
        - Compare years of experience and responsibility level
        - Penalize both underqualified AND overqualified candidates
        - Consider leadership, ownership, and scope

        3.5 Seniority Fit Reasoning:
        - Provide a brief explanation for the seniority fit score, considering years of experience, leadership, and scope. Max 300 characters.

        4. Keyword Match (ATS Simulation):
        - Measure how well the resume matches job description terminology
        - Include semantic similarity, not just exact keyword matches
        - Penalize missing important terms commonly used in the job description

        4.5 Keyword Match Reasoning:
        - Provide a brief explanation for the keyword match score, noting key terms that were well-matched and important terms that were missing. Max 300 characters.

        IMPORTANT:
        - Be objective and critical
        - Avoid giving all high scores
        - Use the full range (0-100)
        - Base decisions ONLY on provided data
        - Do NOT assume missing information

        OUTPUT:
        Return ONLY the structured JSON with the scores. Do not change the format or add any additional commentary outside of the reasoning fields keep it strictly within the JSON structure like this:
        {
            "skills_match": 0,
            "skills_match_reasoning": "",
            "experience_relevance": 0,
            "experience_relevance_reasoning": "",
            "seniority_fit": 0,
            "seniority_fit_reasoning": "",
            "keyword_match": 0,
            "keyword_match_reasoning": ""
        }
        TEXT;
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
            // Skills Match
            'skills_match' => $schema->number()->description('A percentage rating of how well the candidate\'s skills match the job requirements.'),
            'skills_match_reasoning' => $schema->string()->description('A brief explanation of the skills match rating, highlighting key matches and critical missing skills. Max 300 characters.'),
            // Experience Relevance
            'experience_relevance' => $schema->number()->description('A percentage rating of how relevant the candidate\'s work experience is to the job description.'),
            'experience_relevance_reasoning' => $schema->string()->description('A brief explanation of the experience relevance rating, noting specific relevant roles and any irrelevant experience. Max 300 characters.'),
            // Seniority / Level Fit (%)
            'seniority_fit' => $schema->number()->description('A percentage rating of how well the candidate\'s seniority level matches the job requirements.'),
            'seniority_fit_reasoning' => $schema->string()->description('A brief explanation of the seniority fit rating, considering years of experience, leadership roles, and responsibility level. Max 300 characters.'),
            // Keyword / ATS Match (%)
            'keyword_match' => $schema->number()->description('A percentage rating of how well the candidate\'s resume matches the keywords in the job description, simulating an ATS screening.'),
            'keyword_match_reasoning' => $schema->string()->description('A brief explanation of the keyword match rating, noting key terms that were well-matched and important terms that were missing. Max 300 characters.'),
        ];
    }
}
