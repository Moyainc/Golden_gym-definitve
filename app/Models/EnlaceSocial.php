<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnlaceSocial extends Model
{
    protected $table = 'social_links';

    protected $fillable = [
        'facebook',
        'instagram',
        'twitter',
        'youtube',
    ];
}
