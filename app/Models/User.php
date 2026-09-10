<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password', 'avatar', 'cv', 'address', 'zip', 'city', 'phone', 'birthdate', 'job_title', 'max_distance', 'keywords', 'skills', 'auto_match_new_jobs', 'notify_skills_match_threshold', 'notify_experience_relevance_threshold', 'notify_seniority_fit_threshold', 'notify_keyword_match_threshold', 'notify_match_mode'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthdate' => 'date',
            'keywords' => 'array',
            'skills' => 'array',
            'auto_match_new_jobs' => 'boolean',
            'notify_skills_match_threshold' => 'float',
            'notify_experience_relevance_threshold' => 'float',
            'notify_seniority_fit_threshold' => 'float',
            'notify_keyword_match_threshold' => 'float',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
