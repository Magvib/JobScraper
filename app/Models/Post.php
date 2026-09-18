<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
        return $query->where('is_archived', false)->where(fn ($q) => $q->whereNull('deadline_at')->orWhere('deadline_at', '>', now()));
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
                'canonical_url'    => $data['share_url'],
                'is_remote'        => $data['home_workplace'],
                'deadline_is_asap' => $data['apply_deadline_asap'],
                'published_at'     => $data['firstdate'],
                'deadline_at'      => $data['apply_deadline'] ?? $data['lastdate'],
                'is_archived'      => $data['is_archived'],
                'raw'              => $data,
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

    public function getLocation()
    {
        return join(', ', array_filter([$this->city, $this->zipcode, $this->street, $this->country]));
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

            if ($user->address && $user->max_distance) {
                $data['address'] = $user->address.', '.$user->zip.' '.$user->city;
                $data['radius'] = $user->max_distance;
            }

            foreach ($keywords as $keyword) {
                $data['q'] = $keyword;
                try {
                    $response = Http::retry(2, 200)
                        ->connectTimeout(5)
                        ->timeout(15)->get('https://www.jobindex.dk/api/jobsearch/v3', $data)->json()['results'] ?? [];

                    foreach ($response as $job) {
                        Post::savePost($job, PostSource::JOBINDEX, $keyword);
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
                            'resultsPerPage' => 20,
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