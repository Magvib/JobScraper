<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Link extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prettifyUrl()
    {
        $url = $this->url;

        if (Str::contains($url, 'www.')) {
            $url = Str::replaceFirst('www.', '', $url);
        }

        if (Str::contains($url, 'http://')) {
            $url = Str::replaceFirst('http://', '', $url);
        }

        if (Str::contains($url, 'https://')) {
            $url = Str::replaceFirst('https://', '', $url);
        }

        $url = rtrim($url, '/');

        return $url;
    }
}