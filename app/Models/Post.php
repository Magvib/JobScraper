<?php

namespace App\Models;

use App\Jobs\ProcessPostQuestions;
use DOMDocument;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

enum PostSource: string
{
    case JOBINDEX = 'jobindex';
    case JOBNET = 'jobnet';
}

class Post extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'is_remote' => 'boolean',
        'is_part_time' => 'boolean',
        'deadline_is_asap' => 'boolean',
        'is_archived' => 'boolean',
        'published_at' => 'datetime',
        'deadline_at' => 'datetime',
        'raw' => 'array',
    ];

    #[Scope]
    protected function active(Builder $query)
    {
        return $query
            ->where('is_archived', false)
            ->where(fn ($q) => $q->whereNull('deadline_at')->orWhere('deadline_at', '>', now()))
            ->where('updated_at', '>', now()->subDays(2)) // Only include posts updated in the last 2 days
        ;
    }

    public static function savePost($data, PostSource $source, $keyword = null)
    {
        if ($source === PostSource::JOBINDEX) {
            // Jobindex
            self::updateOrCreate(['source' => 'jobindex', 'source_id' => $data['tid']], [
                'keyword'          => $keyword,
                'title'            => $data['headline'],
                'company_name'     => $data['workplace_company']['name'] ?? $data['companytext'],
                'company_logo_url' => $data['workplace_company']['logo'] ?? null,
                'company_website'  => $data['workplace_company']['homeurl'] ?? null,
                'street'           => $data['addresses'][0]['line'] ?? null,
                'city'             => $data['addresses'][0]['city'] ?? $data['area'] ?? null,
                'zipcode'          => $data['addresses'][0]['zipcode'] ?? null,
                'latitude'         => $data['addresses'][0]['coordinates']['latitude'] ?? null,
                'longitude'        => $data['addresses'][0]['coordinates']['longitude'] ?? null,
                'canonical_url'    => $data['url'] ?? $data['share_url'] ?? null,
                'is_remote'        => $data['home_workplace'],
                'deadline_is_asap' => $data['apply_deadline_asap'],
                'published_at'     => $data['firstdate'],
                'deadline_at'      => $data['apply_deadline'] ?? $data['lastdate'],
                'is_archived'      => $data['is_archived'],
                'raw'              => $data,
                'updated_at'       => now(),
            ]);
        } elseif ($source === PostSource::JOBNET) {
            // Jobnet
            self::updateOrCreate(['source' => 'jobnet', 'source_id' => $data['jobAdId']], [
                'keyword'          => $keyword,
                'title'            => $data['title'],
                'description'      => $data['description'],
                'company_name'     => $data['hiringOrgName'],
                'cvr'              => $data['cvr'],
                'canonical_url'    => $data['jobAdUrl'] ?: "https://jobnet.dk/find-job/" . ($data['jobAdId'] ?: ''),
                'occupation'       => $data['occupation'],
                'is_part_time'     => $data['workHourPartTime'],
                'published_at'     => $data['publicationDate'],
                'deadline_at'      => $data['applicationDeadline'],
                'raw'              => $data,
                'company_logo_url' => isset($data['logoUrl']) ? "https://jobnet.dk".$data['logoUrl'] : null,
                'city'             => $data['postalDistrictName'] ?? $data['municipality'] ?? null,
                'zipcode'          => $data['postalCode'] ?? null,
                'updated_at'       => now(),
            ]);
        }
    }

    public function rating()
    {
        return $this->hasOne(JobRating::class, 'job_id', 'source_id')->where('source', $this->source)->where('user_id', auth()->id());
    }

    public function ratingFor(User $user)
    {
        return $this->hasOne(JobRating::class, 'job_id', 'source_id')->where('source', $this->source)->where('user_id', $user->id);
    }

    public function questionAnswers()
    {
        return $this->hasOne(PostQuestionAnswer::class, 'job_id', 'source_id')->where('source', $this->source)->where('user_id', auth()->id());
    }

    public function questionAnswersFor(User $user)
    {
        return $this->hasOne(PostQuestionAnswer::class, 'job_id', 'source_id')->where('source', $this->source)->where('user_id', $user->id);
    }

    /**
     * Trigger (or re-run) AI answering of the user's custom questions for this post.
     */
    public function answerQuestions(User $user): ?PostQuestionAnswer
    {
        if (empty($user->questions)) {
            return null;
        }

        $this->questionAnswers()->delete();

        $this->questionAnswers()->create([
            'user_id' => $user->id,
            'job_id' => $this->source_id,
            'source' => $this->source,
        ]);

        ProcessPostQuestions::dispatch($this, $user);

        return $this->questionAnswers()->first();
    }

    /**
     * The saved answers row for the user, or null when the questions were never answered.
     */
    public function questionAnswersForUser(User $user): ?PostQuestionAnswer
    {
        return $this->questionAnswersFor($user)->first();
    }

    public function getLocation()
    {
        return join(', ', array_filter([$this->city, $this->zipcode, $this->street, $this->country]));
    }

    /**
     * The stored description, or fetch and clean the job page once and persist it.
     */
    public function fetchDescription($force = false): ?string
    {
        if ($this->description && ! $force) {
            return $this->description;
        }

        if (! $this->canonical_url) {
            return null;
        }

        if ($force) {
            Cache::forget('job_description_'.md5($this->canonical_url));
        }

        $html = Cache::remember('job_description_'.md5($this->canonical_url), now()->addHours(6), function () {
            return $this->fetchDescriptionBody($this->canonical_url);
        });

        if (! $html) {
            return null;
        }

        $this->description = $html;
        $this->save();

        return $html;
    }

    private function fetchDescriptionBody(string $jobUrl): ?string
    {
        try {
            $response = Http::retry(2, 200)
                ->connectTimeout(5)
                ->timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                ])
                ->get($jobUrl);

            if (! $response->successful()) {
                Log::warning('Job description fetch failed.', [
                    'job_url' => $jobUrl,
                    'status' => $response->status(),
                ]);

                return null;
            }

            return $this->cleanHtmlBody($response->body());
        } catch (\Throwable $th) {
            Log::warning('Job description fetch exception.', [
                'job_url' => $jobUrl,
                'error' => $th->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Strip page chrome (scripts, styles, navigation) so only the ad markup remains.
     */
    private function cleanHtmlBody(string $html): ?string
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        foreach (['script', 'style', 'noscript', 'nav', 'header', 'footer', 'aside', 'iframe', 'svg'] as $tag) {
            $nodes = $dom->getElementsByTagName($tag);
            for ($i = $nodes->length - 1; $i >= 0; $i--) {
                $nodes->item($i)->parentNode?->removeChild($nodes->item($i));
            }
        }

        $body = $dom->getElementsByTagName('body')->item(0);

        if (! $body) {
            return null;
        }

        $cleaned = '';
        foreach ($body->childNodes as $node) {
            $cleaned .= $dom->saveHTML($node);
        }

        return trim($cleaned) ?: null;
    }

    public static function searchForJobs(User $user, $clearCache = false)
    {
        $cacheTime = now()->addMinutes(60);
        $keywords = collect($user->keywords);
        $jobIndexKey = 'jobindex_'.md5(json_encode($keywords->toArray()));
        $jobnetKey = 'jobnet_'.md5(json_encode($keywords->toArray()));

        if ($clearCache) {
            Cache::forget($jobIndexKey);
            Cache::forget($jobnetKey);
        }

        Cache::remember($jobIndexKey, $cacheTime, function () use ($user, $keywords) {
            $data = [
                'sort' => 'date',
            ];

            $maxPages = 5;

            foreach ($keywords as $keyword) {
                $data['q'] = $keyword;

                try {
                    $page = 1;
                    $totalPages = 1;

                    while ($page <= min($totalPages, $maxPages)) {
                        $requestData = $page === 1 ? $data : $data + ['page' => $page];

                        $response = Http::retry(2, 200)
                            ->connectTimeout(5)
                            ->timeout(15)->get('https://www.jobindex.dk/api/jobsearch/v3', $requestData)->json();

                        $totalPages = $response['total_pages'] ?? 1;

                        foreach ($response['results'] ?? [] as $job) {
                            Post::savePost($job, PostSource::JOBINDEX, $keyword);
                        }

                        $page++;
                    }
                } catch (\Throwable $th) {
                    continue;
                }
            }

            return 1;
        });

        // Jobnet only supports one search string per request, so one request per keyword.
        Cache::remember($jobnetKey, $cacheTime, function () use ($keywords) {
            foreach ($keywords as $keyword) {
                try {
                    $response = Http::retry(2, 200)
                        ->connectTimeout(5)
                        ->timeout(15)
                        ->withHeaders([
                            'x-csrf' => 1,
                        ])
                        ->get('https://jobnet.dk/bff/FindJob/Search', [
                            'resultsPerPage' => 200,
                            'pageNumber' => 1,
                            'orderType' => 'BestMatch',
                            'searchString' => $keyword,
                        ])->json() ?? [];

                    foreach ($response['jobAds'] ?? [] as $job) {
                        Post::savePost($job, PostSource::JOBNET, $keyword);
                    }
                } catch (\Throwable $th) {
                    continue;
                }
            }

            return 1;
        });
    }
}