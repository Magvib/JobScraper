<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoverLetter extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function job()
    {
        return $this->belongsTo(Post::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function renderContext()
    {
        $context = "";

        foreach (preg_split('/\n{2,}/', trim($this->content)) as $paragraph) {
            $context .= '<p class="text-sm leading-relaxed text-justify">';
            $context .= implode('<br>', preg_split('/\n/', trim($paragraph)));
            $context .= '</p>';
        }

        return $context;
    }
}
