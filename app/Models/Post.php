<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function scopeActive($query)
    {
        return $query->where('is_archived', false)->where(fn ($q) => $q->whereNull('deadline_at')->orWhere('deadline_at', '>', now()));
    }

    public static function savePost($data, PostSource $source)
    {
        if ($source === PostSource::JOBINDEX) {
            // Jobindex
            self::updateOrCreate(['source' => 'jobindex', 'source_id' => $data['tid']], [
                'title'            => $data['headline'],
                'company_name'     => $data['workplace_company']['name'] ?? $data['companytext'],
                'company_logo_url' => $data['workplace_company']['logo'] ?? null,
                'company_website'  => $data['workplace_company']['homeurl'] ?? null,
                'street'           => $data['addresses'][0]['line'] ?? null,
                'city'             => $data['addresses'][0]['city'] ?? null,
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
        return $this->city . ($this->zipcode ? ', ' . $this->zipcode : '') . ($this->street ? ', ' . $this->street : '') . ($this->country ? ', ' . $this->country : '');
    }
}