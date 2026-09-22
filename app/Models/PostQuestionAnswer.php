<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostQuestionAnswer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'answers' => 'array',
    ];
}