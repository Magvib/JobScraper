@component('mail::message')
# New job match found

Hi {{ $user->name }},

A new job matched your preferences.

Job title: {{ $jobRating->job_title ?? $jobRating->job_id }}

**Match scores**

- Skills match: {{ $jobRating->skills_match ?? 'N/A' }}%
- Experience relevance: {{ $jobRating->experience_relevance ?? 'N/A' }}%
- Seniority fit: {{ $jobRating->seniority_fit ?? 'N/A' }}%
- Keyword match: {{ $jobRating->keyword_match ?? 'N/A' }}%

Job reference: {{ $jobRating->job_id }}

Thanks,
{{ config('app.name') }}
@endcomponent
