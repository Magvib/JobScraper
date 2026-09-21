# JobScraper

A Laravel + Livewire application for job searching and CV building. Users sign in with GitHub, build their CV and cover letters in-app, and get AI-scored matches from [jobnet.dk](https://jobnet.dk) and [jobindex.dk](https://jobindex.dk) listings.

## Features

- **CV builder** (`/cv`) — build your CV from structured data instead of uploading a file
- **Cover letters** (`/cover-letter`) — AI-assisted cover letter generation
- **Job matching** (`/jobs`) — jobs scraped from jobnet.dk, rated by AI against your CV
- **Auto-match** — opt-in daily command that fetches and rates the newest matching jobs each morning
- **CV templates** (`/templates`) — 170+ printable CV templates, previewable without login and exportable as PDF via Gotenberg
- **Dashboard** (`/dashboard`) — match stats, top matches, and recent scoring activity
- **Downloadable templates** — shareable, CV/cover letter files

## Requirements

- PHP >= 8.3
- Composer
- Node.js + npm
- SQLite (default) or another database supported by Laravel

## Getting started

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

## Development

Run the app, queue worker, log tail and Vite dev server together:

```bash
composer dev
```

## Authentication

Login is GitHub OAuth (Laravel Socialite). Configure in `.env`:

```
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI=
```

Routes: `/auth/redirect` starts the flow, `/auth/callback` handles the response, `/logout` signs out.

## External services

| Service | Purpose | Env vars |
| --- | --- | --- |
| OpenRouter | AI agents for job rating, keyword extraction, cover letters | `OPENROUTER_API_KEY` |
| Gotenberg | PDF rendering of CV/cover letter templates | `GOTENBERG_API_URL`, `GOTENBERG_API_BASIC_AUTH_USERNAME`, `GOTENBERG_API_BASIC_AUTH_PASSWORD` |

## Scheduled jobs

`php artisan jobs:auto-match` — for each user with auto-match enabled, fetches the newest jobs from the jobnet.dk API (`https://jobnet.dk/bff/FindJob/Search`) and queues `ProcessJobRating` jobs to score them against the user's CV with AI.

## Project structure

- `app/Ai/Agents` — AI agents (resume→job scoring, keyword specialist, cover letter specialist)
- `app/Console/Commands/AutoMatchNewJobs.php` — the `jobs:auto-match` command
- `app/Jobs/ProcessJobRating.php` — queued AI job rating
- `app/Models` — `User`, `JobRating`, `CoverLetter`, `Link`, `Post`
- `resources/views/components/⚡*.blade.php` — single-file Livewire components (dashboard, jobs, profile, cv, templates, cover letter)
- `resources/views/templates` — CV/cover letter blade templates (`temp1`…`temp172`)

## Routes

| Route | Description |
| --- | --- |
| `/` | Landing page with random template showcase |
| `/preview/template/{name}` | Public template preview with fake user |
| `/dashboard` | Auth — match stats and activity |
| `/jobs` | Auth — job listings with AI scores |
| `/cv` | Auth — CV builder |
| `/cover-letter` | Auth — cover letter builder |
| `/profile` | Auth — profile settings (keywords, notifications, auto-match) |
| `/templates` | Auth — template gallery |
| `/template/{name}` | Auth — renders a template with your data |
| `/signed/template/{slug}` | Signed, shareable template URL |

## Testing & linting

```bash
composer test      # Pest
composer lint      # Pint
```