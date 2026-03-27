<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = [
        'playlist_id',
        'title',
        'description',
        'thumbnail',
        'channel_name',
        'lessons_count',
        'view_count',
        'duration_seconds',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }
}
