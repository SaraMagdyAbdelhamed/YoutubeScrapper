<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['title'];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function playlists()
    {
        return $this->belongsToMany(Playlist::class);
    }
}
